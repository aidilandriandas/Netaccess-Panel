<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\VpnUser;
use App\Services\MikrotikL2TPService;
use Illuminate\Http\Request;

class VpnUserController extends Controller
{
    protected MikrotikL2TPService $l2tpService;

    public function __construct(MikrotikL2TPService $l2tpService)
    {
        $this->l2tpService = $l2tpService;
    }

    public function index(Request $request)
    {
        $query = VpnUser::with('customer', 'package');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('assigned_ip', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vpnUsers = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vpn-users.index', compact('vpnUsers'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $packages  = Package::where('is_active', true)->where('service_type', 'vpn')->orderBy('name')->get();
        return view('vpn-users.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id'  => 'required|exists:packages,id',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $package  = Package::findOrFail($validated['package_id']);

        $creds      = $this->l2tpService->generateCredentials($customer->name);
        $assignedIp = $this->l2tpService->getNextAvailableIp();

        $vpnUser = VpnUser::create([
            'customer_id'   => $customer->id,
            'package_id'    => $package->id,
            'username'      => $creds['username'],
            'l2tp_password' => $creds['password'],
            'assigned_ip'   => $assignedIp,
            'status'        => 'active',
            'started_at'    => now(),
            'expired_at'    => now()->addDays($package->duration_days),
        ]);

        $this->l2tpService->addUser($vpnUser);

        // Auto-generate invoice
        Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id'    => $customer->id,
            'package_id'     => $package->id,
            'vpn_user_id'    => $vpnUser->id,
            'subtotal'       => $package->price,
            'setup_fee'      => $package->setup_fee,
            'discount'       => 0,
            'total'          => $package->price + $package->setup_fee,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(7),
        ]);

        ActivityLog::log('create_vpn_user', "Created L2TP user: {$creds['username']} for {$customer->name}");

        return redirect()->route('vpn-users.show', $vpnUser)
            ->with('success', 'VPN user L2TP berhasil dibuat.');
    }

    public function show(VpnUser $vpnUser)
    {
        $vpnUser->load('customer', 'package', 'invoices');
        $instructions  = $this->l2tpService->createClientInstructions($vpnUser);
        $activeSession = $this->l2tpService->getActiveSession($vpnUser->username);
        return view('vpn-users.show', compact('vpnUser', 'instructions', 'activeSession'));
    }

    public function downloadConfig(VpnUser $vpnUser)
    {
        $instructions = $this->l2tpService->createClientInstructions($vpnUser);
        $filename     = $vpnUser->username . '_l2tp.txt';

        return response($instructions)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }





    public function extend(Request $request, VpnUser $vpnUser)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $oldStatus    = $vpnUser->status;
        $currentExpiry = $vpnUser->expired_at ?? now();
        $newExpiry    = $currentExpiry->isPast()
            ? now()->addDays($request->days)
            : $currentExpiry->addDays($request->days);

        $vpnUser->update([
            'expired_at' => $newExpiry,
            'status'     => 'active',
        ]);

        // Unsuspend di Mikrotik kalau sebelumnya suspended/expired
        if (in_array($oldStatus, ['expired', 'suspended'])) {
            $this->l2tpService->unsuspendUser($vpnUser);
        }

        ActivityLog::log('extend_vpn_user', "Extended L2TP user: {$vpnUser->username} by {$request->days} days");

        return back()->with('success', "Masa aktif VPN user diperpanjang {$request->days} hari.");
    }

    public function destroy(VpnUser $vpnUser)
    {
        $username = $vpnUser->username;
        $this->l2tpService->removeUser($vpnUser);
        $vpnUser->delete();
        ActivityLog::log('delete_vpn_user', "Deleted L2TP user: {$username}");

        return redirect()->route('vpn-users.index')
            ->with('success', 'VPN user berhasil dihapus.');
    }








    public function suspend(\App\Models\VpnUser $vpnUser)
    {
        try {
            $service = app(\App\Services\MikrotikL2TPService::class);

            $success = $service->suspendUser($vpnUser);

            if (!$success) {
                \Log::error('VPN suspend failed on MikroTik', [
                    'vpn_user_id' => $vpnUser->id,
                    'username' => $vpnUser->username,
                ]);

                return back()->with('error', 'Gagal suspend di MikroTik. Status panel tidak diubah.');
            }

            $vpnUser->update([
                'status' => 'suspended',
            ]);

            \App\Models\ActivityLog::log(
                'suspend_vpn_user',
                "Suspended VPN user on MikroTik: {$vpnUser->username}"
            );

            return back()->with('success', 'VPN user berhasil di-suspend di MikroTik.');
        } catch (\Throwable $e) {
            \Log::error('Suspend VPN exception', [
                'vpn_user_id' => $vpnUser->id ?? null,
                'username' => $vpnUser->username ?? null,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error suspend VPN: ' . $e->getMessage());
        }
    }

    public function unsuspend(\App\Models\VpnUser $vpnUser)
    {
        try {
            $service = app(\App\Services\MikrotikL2TPService::class);

            $success = $service->unsuspendUser($vpnUser);

            if (!$success) {
                \Log::error('VPN unsuspend failed on MikroTik', [
                    'vpn_user_id' => $vpnUser->id,
                    'username' => $vpnUser->username,
                ]);

                return back()->with('error', 'Gagal unsuspend di MikroTik. Status panel tidak diubah.');
            }

            $vpnUser->update([
                'status' => 'active',
            ]);

            \App\Models\ActivityLog::log(
                'unsuspend_vpn_user',
                "Unsuspended VPN user on MikroTik: {$vpnUser->username}"
            );

            return back()->with('success', 'VPN user berhasil diaktifkan kembali di MikroTik.');
        } catch (\Throwable $e) {
            \Log::error('Unsuspend VPN exception', [
                'vpn_user_id' => $vpnUser->id ?? null,
                'username' => $vpnUser->username ?? null,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error unsuspend VPN: ' . $e->getMessage());
        }
    }

    public function terminate(\App\Models\VpnUser $vpnUser)
    {
        try {
            $service = app(\App\Services\MikrotikL2TPService::class);

            $success = $service->removeUser($vpnUser);

            if (!$success) {
                \Log::error('VPN terminate failed on MikroTik', [
                    'vpn_user_id' => $vpnUser->id,
                    'username' => $vpnUser->username,
                ]);

                return back()->with('error', 'Gagal terminate di MikroTik. Data panel tidak dihapus.');
            }

            $username = $vpnUser->username;

            $vpnUser->delete();

            \App\Models\ActivityLog::log(
                'terminate_vpn_user',
                "Terminated VPN user from MikroTik and panel: {$username}"
            );

            return redirect()->route('vpn-users.index')
                ->with('success', 'VPN user berhasil dihapus dari MikroTik dan panel.');
        } catch (\Throwable $e) {
            \Log::error('Terminate VPN exception', [
                'vpn_user_id' => $vpnUser->id ?? null,
                'username' => $vpnUser->username ?? null,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error terminate VPN: ' . $e->getMessage());
        }
    }

}

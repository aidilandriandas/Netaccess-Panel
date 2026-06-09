<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\HostingAccount;
use App\Models\Package;
use App\Services\Hosting\HostingProvisionerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class HostingAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = HostingAccount::with('customer', 'hostingPackage');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accounts = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('hosting-accounts.index', compact('accounts'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $packages  = Package::where('service_type', 'hosting')->where('is_active', true)->orderBy('name')->get();
        return view('hosting-accounts.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'hosting_package_id' => 'required|exists:packages,id',
            'domain'             => 'required|string|max:255',
            'username'           => 'required|string|max:16|alpha_num|unique:hosting_accounts,username',
            'password'           => 'required|string|min:8|max:255',
            'server_type'        => 'required|in:whm,directadmin',
            'notes'              => 'nullable|string|max:2000',
        ]);

        
        $serverType = $validated['server_type'] ?? 'whm';

        if ($serverType === 'whm') {
            $whmHost = trim((string) DB::table('settings')->where('key', 'whm_host')->value('value'));
            $whmUsername = trim((string) DB::table('settings')->where('key', 'whm_username')->value('value'));
            $whmToken = trim((string) DB::table('settings')->where('key', 'whm_token')->value('value'));

            if ($whmHost === '' || $whmUsername === '' || $whmToken === '') {
                return back()
                    ->withInput()
                    ->with('error', 'Server hosting WHM belum lengkap. Isi WHM Host, WHM Username, dan WHM Token di Settings dulu.');
            }
        }


        $package = Package::where('service_type', 'hosting')->findOrFail($validated['hosting_package_id']);
        $legacyPackageId = $this->syncHostingLegacyPackage($package);

        $account = HostingAccount::create([
            'customer_id'        => $validated['customer_id'],
            'hosting_package_id' => $legacyPackageId,
            'package_id' => $package->id,
            'domain'             => $validated['domain'],
            'username'           => $validated['username'],
            'password'           => $validated['password'],
            'server_type'        => $validated['server_type'],
            'status'             => 'pending',
            'started_at'         => now(),
            'expired_at'         => now()->addDays($package->duration_days),
            'notes'              => $validated['notes'] ?? null,
        ]);

        ActivityLog::log('create_hosting_account', "Created hosting account: {$account->username} ({$account->domain})");

        return redirect()->route('hosting-accounts.index')
            ->with('success', 'Akun hosting berhasil dibuat. Gunakan tombol Provision untuk membuat akun di server.');
    }

    public function show(HostingAccount $hostingAccount)
    {
        $hostingAccount->load('customer', 'hostingPackage', 'hostingOrders');
        return view('hosting-accounts.show', compact('hostingAccount'));
    }

    public function provision(HostingAccount $hostingAccount)
    {
        try {
            $provisioner = HostingProvisionerFactory::make($hostingAccount->server_type);
            $result = $provisioner->createAccount($hostingAccount);

            if ($result['success']) {
                $hostingAccount->update(['status' => 'active']);
                ActivityLog::log('provision_hosting', "Provisioned hosting: {$hostingAccount->username}");
                return back()->with('success', 'Akun hosting berhasil di-provision: ' . $result['message']);
            }

            ActivityLog::log('provision_hosting_failed', "Failed to provision: {$hostingAccount->username} - {$result['message']}");
            return back()->with('error', 'Gagal provision: ' . $result['message']);
        } catch (\Throwable $e) {
            ActivityLog::log('provision_hosting_error', "Error provisioning {$hostingAccount->username}: {$e->getMessage()}");
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function suspend(HostingAccount $hostingAccount)
    {
        try {
            $provisioner = HostingProvisionerFactory::make($hostingAccount->server_type);
            $result = $provisioner->suspendAccount($hostingAccount);

            if ($result['success']) {
                $hostingAccount->update(['status' => 'suspended']);
                ActivityLog::log('suspend_hosting', "Suspended hosting: {$hostingAccount->username}");
                return back()->with('success', 'Akun hosting berhasil di-suspend.');
            }

            return back()->with('error', 'Gagal suspend: ' . $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function unsuspend(HostingAccount $hostingAccount)
    {
        try {
            $provisioner = HostingProvisionerFactory::make($hostingAccount->server_type);
            $result = $provisioner->unsuspendAccount($hostingAccount);

            if ($result['success']) {
                $hostingAccount->update(['status' => 'active']);
                ActivityLog::log('unsuspend_hosting', "Unsuspended hosting: {$hostingAccount->username}");
                return back()->with('success', 'Akun hosting berhasil di-unsuspend.');
            }

            return back()->with('error', 'Gagal unsuspend: ' . $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function terminate(HostingAccount $hostingAccount)
    {
        try {
            $provisioner = HostingProvisionerFactory::make($hostingAccount->server_type);
            $result = $provisioner->terminateAccount($hostingAccount);

            if ($result['success']) {
                $hostingAccount->update(['status' => 'terminated']);
                ActivityLog::log('terminate_hosting', "Terminated hosting: {$hostingAccount->username}");
                return back()->with('success', 'Akun hosting berhasil di-terminate.');
            }

            return back()->with('error', 'Gagal terminate: ' . $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(HostingAccount $hostingAccount)
    {
        $name = $hostingAccount->username;
        $hostingAccount->delete();
        ActivityLog::log('delete_hosting_account', "Deleted hosting account: {$name}");

        return redirect()->route('hosting-accounts.index')
            ->with('success', 'Data akun hosting berhasil dihapus.');
    }

    public function edit($id)
    {
        $account = HostingAccount::findOrFail($id);

        return view('hosting-accounts.edit', compact('account'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $account = HostingAccount::findOrFail($id);

        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'server_type' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $account->update($validated);

        if (class_exists(\App\Models\ActivityLog::class)) {
            \App\Models\ActivityLog::log('update_hosting_account', 'Updated hosting account: ' . ($account->domain ?? $account->id));
        }

        return redirect()
            ->route('hosting-accounts.index')
            ->with('success', 'Hosting account berhasil diperbarui.');
    }


    protected function syncHostingLegacyPackage($package): int
    {
        if (!Schema::hasTable('hosting_packages')) {
            return (int) $package->id;
        }

        $now = now();

        DB::table('hosting_packages')->updateOrInsert(
            ['id' => $package->id],
            [
                'name' => $package->name,
                'price' => $package->price ?? 0,
                'setup_fee' => $package->setup_fee ?? 0,
                'duration_days' => $package->duration_days ?? 30,
                'disk_space_mb' => 1000,
                'bandwidth_mb' => null,
                'max_email_accounts' => max((int)($package->max_users ?? 1), 1),
                'max_databases' => 3,
                'max_addon_domains' => 0,
                'max_parked_domains' => 0,
                'max_subdomains' => 5,
                'cpanel_package' => $package->name,
                'description' => $package->description,
                'is_active' => (bool)($package->is_active ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) $package->id;
    }


}

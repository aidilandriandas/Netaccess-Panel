<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\VpsServer;
use App\Models\VpsService;
use App\Models\Package;
use App\Services\Vps\VpsProvisioningService;
use Illuminate\Http\Request;

class VpsServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = VpsService::with('customer', 'vpsPackage', 'vpsServer')
            ->when($request->search, fn ($q, $s) => $q->where('hostname', 'like', "%{$s}%")->orWhere('main_ip', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vps-services.index', compact('services'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $packages = Package::where('service_type', 'vps')->where('is_active', true)->orderBy('name')->get();
        $servers = VpsServer::where('status', 'active')->orderBy('name')->get();

        return view('vps-services.create', compact('customers', 'packages', 'servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vps_package_id' => 'required|exists:packages,id',
            'vps_server_id' => 'nullable|exists:vps_servers,id',
            'hostname' => 'required|string|max:255|regex:/^[a-zA-Z0-9][a-zA-Z0-9\.\-]+$/',
            'main_ip' => 'nullable|ip',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'os_name' => 'nullable|string|max:255',
            'cpu_cores' => 'required|integer|min:1',
            'ram_mb' => 'required|integer|min:128',
            'storage_gb' => 'required|integer|min:1',
            'bandwidth_gb' => 'nullable|integer|min:1',
            'status' => 'required|in:pending,active,suspended,expired,terminated,failed',
            'started_at' => 'nullable|date',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['status'] === 'active' && !empty($validated['started_at'])) {
            $validated['provisioned_at'] = now();
        }

        $service = VpsService::create($validated);
        ActivityLog::log('create_vps_service', "Created VPS service: {$service->hostname} for customer #{$service->customer_id}");

        return redirect()->route('vps-services.index')->with('success', 'VPS Service berhasil dibuat.');
    }

    public function show(VpsService $vpsService)
    {
        $vpsService->load('customer', 'vpsPackage', 'vpsServer', 'vpsOrders.invoice');
        $canViewPassword = Setting::get('vps_client_can_view_password', 'false') === 'true';

        return view('vps-services.show', ['service' => $vpsService, 'canViewPassword' => $canViewPassword]);
    }

    public function edit(VpsService $vpsService)
    {
        $customers = Customer::orderBy('name')->get();
        $packages = Package::where('service_type', 'vps')->orderBy('name')->get();
        $servers = VpsServer::orderBy('name')->get();

        return view('vps-services.edit', [
            'service' => $vpsService,
            'customers' => $customers,
            'packages' => $packages,
            'servers' => $servers,
        ]);
    }

    public function update(Request $request, VpsService $vpsService)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vps_package_id' => 'required|exists:packages,id',
            'vps_server_id' => 'nullable|exists:vps_servers,id',
            'hostname' => 'required|string|max:255|regex:/^[a-zA-Z0-9][a-zA-Z0-9\.\-]+$/',
            'main_ip' => 'nullable|ip',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'os_name' => 'nullable|string|max:255',
            'cpu_cores' => 'required|integer|min:1',
            'ram_mb' => 'required|integer|min:128',
            'storage_gb' => 'required|integer|min:1',
            'bandwidth_gb' => 'nullable|integer|min:1',
            'status' => 'required|in:pending,active,suspended,expired,terminated,failed',
            'started_at' => 'nullable|date',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $vpsService->update($validated);
        ActivityLog::log('update_vps_service', "Updated VPS service: {$vpsService->hostname}");

        return redirect()->route('vps-services.show', $vpsService)->with('success', 'VPS Service berhasil diperbarui.');
    }

    public function suspend(VpsService $vpsService)
    {
        try {
            $provider = VpsProvisioningService::make($vpsService->vpsServer?->provider_type);
            $result = $provider->suspendInstance($vpsService);
            ActivityLog::log('suspend_vps', "Suspended VPS: {$vpsService->hostname}");

            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            ActivityLog::log('suspend_vps_failed', "Failed to suspend VPS: {$vpsService->hostname} - {$e->getMessage()}");
            return back()->with('error', 'Gagal suspend VPS: ' . $e->getMessage());
        }
    }

    public function unsuspend(VpsService $vpsService)
    {
        try {
            $provider = VpsProvisioningService::make($vpsService->vpsServer?->provider_type);
            $result = $provider->unsuspendInstance($vpsService);
            ActivityLog::log('unsuspend_vps', "Unsuspended VPS: {$vpsService->hostname}");

            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            ActivityLog::log('unsuspend_vps_failed', "Failed to unsuspend VPS: {$vpsService->hostname} - {$e->getMessage()}");
            return back()->with('error', 'Gagal unsuspend VPS: ' . $e->getMessage());
        }
    }

    public function terminate(VpsService $vpsService)
    {
        try {
            $provider = VpsProvisioningService::make($vpsService->vpsServer?->provider_type);
            $result = $provider->terminateInstance($vpsService);
            ActivityLog::log('terminate_vps', "Terminated VPS: {$vpsService->hostname}");

            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            ActivityLog::log('terminate_vps_failed', "Failed to terminate VPS: {$vpsService->hostname} - {$e->getMessage()}");
            return back()->with('error', 'Gagal terminate VPS: ' . $e->getMessage());
        }
    }

    public function extend(Request $request, VpsService $vpsService)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $days = (int) $validated['days'];
	$newExpiry = ($vpsService->expired_at ?? now())->addDays($days);
        $vpsService->update(['expired_at' => $newExpiry]);
        ActivityLog::log('extend_vps', "Extended VPS: {$vpsService->hostname} by {$validated['days']} days to {$newExpiry->format('Y-m-d')}");

        return back()->with('success', "VPS berhasil diperpanjang {$validated['days']} hari.");
    }

    public function destroy(VpsService $vpsService)
    {
        $hostname = $vpsService->hostname;
        $vpsService->delete();
        ActivityLog::log('delete_vps_service', "Deleted VPS service: {$hostname}");

        return redirect()->route('vps-services.index')->with('success', 'VPS Service berhasil dihapus.');
    }
}

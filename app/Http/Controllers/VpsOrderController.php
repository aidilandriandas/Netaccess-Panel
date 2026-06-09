<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\VpsOrder;
use App\Models\VpsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VpsOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = VpsOrder::with('customer', 'vpsPackage', 'invoice')
            ->when($request->search, fn ($q, $s) => $q->where('order_number', 'like', "%{$s}%")->orWhere('hostname', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vps-orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $packages = Package::where('service_type', 'vps')->where('is_active', true)->orderBy('name')->get();

        return view('vps-orders.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vps_package_id' => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'hostname' => 'required|string|max:255|regex:/^[a-zA-Z0-9][a-zA-Z0-9\.\-]+$/',
            'os_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $package = Package::where('service_type', 'vps')->findOrFail($validated['vps_package_id']);
        $legacyPackageId = $this->syncVpsLegacyPackage($package);
        $validated['vps_package_id'] = $legacyPackageId;
        $multiplier = match ($validated['billing_cycle']) {
            'quarterly' => 3,
            'yearly' => 12,
            default => 1,
        };
        $validated['price'] = $package->price * $multiplier;
        $validated['order_number'] = VpsOrder::generateOrderNumber();
        $validated['status'] = 'pending';

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $validated['customer_id'],
	    'package_id' => $package->id,
            'subtotal' => $validated['price'],
            'setup_fee' => 0,
            'discount' => 0,
            'total' => $validated['price'],
            'status' => 'unpaid',
            'due_date' => now()->addDays(3),
            'notes' => "VPS Order: {$validated['order_number']} - {$package->name} ({$validated['billing_cycle']})",
        ]);

        $validated['invoice_id'] = $invoice->id;
        $validated['status'] = 'unpaid';

        $order = VpsOrder::create($validated);
        ActivityLog::log('create_vps_order', "Created VPS order: {$order->order_number} for customer #{$order->customer_id}");

        return redirect()->route('vps-orders.show', $order)->with('success', 'VPS Order berhasil dibuat. Invoice telah digenerate.');
    }

    public function show(VpsOrder $vpsOrder)
    {
        $vpsOrder->load('customer', 'vpsPackage', 'vpsService', 'invoice');
        return view('vps-orders.show', ['order' => $vpsOrder]);
    }

    public function provision(Request $request, VpsOrder $vpsOrder)
    {
        if (!in_array($vpsOrder->status, ['paid', 'pending', 'unpaid'])) {
            return back()->with('error', 'Order tidak bisa diprovision dengan status saat ini.');
        }

        $validated = $request->validate([
            'main_ip' => 'required|ip',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
            'os_name' => 'required|string|max:255',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'vps_server_id' => 'nullable|exists:vps_servers,id',
            'expired_at' => 'required|date|after:today',
        ]);

        $package = $vpsOrder->vpsPackage;

        $service = VpsService::create([
            'customer_id' => $vpsOrder->customer_id,
            'vps_package_id' => $vpsOrder->vps_package_id,
            'vps_server_id' => $validated['vps_server_id'],
            'hostname' => $vpsOrder->hostname,
            'main_ip' => $validated['main_ip'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'ssh_port' => $validated['ssh_port'],
            'os_name' => $validated['os_name'],
            'cpu_cores' => $package->cpu_cores,
            'ram_mb' => $package->ram_mb,
            'storage_gb' => $package->storage_gb,
            'bandwidth_gb' => $package->bandwidth_gb,
            'status' => 'active',
            'started_at' => now(),
            'expired_at' => $validated['expired_at'],
            'provisioned_at' => now(),
        ]);

        $vpsOrder->update([
            'vps_service_id' => $service->id,
            'status' => 'active',
        ]);

        if ($vpsOrder->invoice && $vpsOrder->invoice->status !== 'paid') {
            $vpsOrder->invoice->update(['status' => 'paid', 'paid_at' => now()]);
        }

        ActivityLog::log('provision_vps_order', "Provisioned VPS order: {$vpsOrder->order_number} → service #{$service->id}");

        return redirect()->route('vps-services.show', $service)->with('success', 'VPS berhasil diprovision dan service aktif.');
    }

    public function cancel(VpsOrder $vpsOrder)
    {
        if (in_array($vpsOrder->status, ['active', 'cancelled'])) {
            return back()->with('error', 'Order tidak bisa dibatalkan.');
        }

        $vpsOrder->update(['status' => 'cancelled']);

        if ($vpsOrder->invoice && $vpsOrder->invoice->status === 'unpaid') {
            $vpsOrder->invoice->update(['status' => 'cancelled']);
        }

        ActivityLog::log('cancel_vps_order', "Cancelled VPS order: {$vpsOrder->order_number}");

        return back()->with('success', 'VPS Order berhasil dibatalkan.');
    }

    public function destroy(\App\Models\VpsOrder $vpsOrder)
    {
        if (in_array($vpsOrder->status, ['active', 'provisioning'])) {
            return back()->with('error', 'VPS order aktif/provisioning tidak bisa dihapus. Cancel dulu.');
        }

        $orderNumber = $vpsOrder->order_number ?? $vpsOrder->id;

        $vpsOrder->delete();

        \App\Models\ActivityLog::log('delete_vps_order', "Deleted VPS order: {$orderNumber}");

        return redirect()->route('vps-orders.index')
            ->with('success', 'VPS order berhasil dihapus.');
    }


    public function updateStatus(\Illuminate\Http\Request $request, \App\Models\VpsOrder $vpsOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,unpaid,paid,provisioning,active,failed,cancelled',
        ]);

        $oldStatus = $vpsOrder->status;
        $newStatus = $validated['status'];

        $vpsOrder->update([
            'status' => $newStatus,
        ]);

        if ($vpsOrder->invoice) {
            if (in_array($newStatus, ['paid', 'active'])) {
                $vpsOrder->invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            if ($newStatus === 'cancelled') {
                $vpsOrder->invoice->update([
                    'status' => 'cancelled',
                ]);
            }
        }

        \App\Models\ActivityLog::log(
            'update_vps_order_status',
            "Updated VPS order {$vpsOrder->order_number} status from {$oldStatus} to {$newStatus}"
        );

        return back()->with('success', "Status VPS order berhasil diubah ke {$newStatus}.");
    }




    protected function syncVpsLegacyPackage($package): int
    {
        if (!Schema::hasTable('vps_packages')) {
            return (int) $package->id;
        }

        $now = now();

        DB::table('vps_packages')->updateOrInsert(
            ['id' => $package->id],
            [
                'name' => $package->name,
                'description' => $package->description,
                'price' => $package->price ?? 0,
                'billing_cycle' => 'monthly',
                'cpu_cores' => 1,
                'ram_mb' => 1024,
                'storage_gb' => 20,
                'bandwidth_gb' => null,
                'ipv4_count' => 1,
                'os_options' => json_encode(['Ubuntu 22.04', 'Ubuntu 24.04', 'Debian 12']),
                'is_active' => (bool)($package->is_active ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) $package->id;
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\HostingAccount;
use App\Models\HostingOrder;
use App\Models\Invoice;
use App\Models\Package;
use App\Services\Hosting\HostingProvisionerFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HostingOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = HostingOrder::with('customer', 'hostingPackage', 'hostingAccount');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('hosting-orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $packages  = Package::where('service_type', 'hosting')->where('is_active', true)->orderBy('name')->get();
        return view('hosting-orders.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'hosting_package_id' => 'required|exists:packages,id',
            'domain'             => 'required|string|max:255',
            'notes'              => 'nullable|string|max:2000',
        ]);

        $package  = Package::where('service_type', 'hosting')->findOrFail($validated['hosting_package_id']);
        $legacyPackageId = $this->syncHostingLegacyPackage($package);
        $customer = Customer::findOrFail($validated['customer_id']);

        $total = $package->price + $package->setup_fee;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id'    => $customer->id,
            'package_id'     => $package->id,
            'subtotal'       => $package->price,
            'setup_fee'      => $package->setup_fee,
            'discount'       => 0,
            'total'          => $total,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(7),
            'notes'          => "Hosting order: {$validated['domain']}",
        ]);

        $order = HostingOrder::create([
            'order_number'       => HostingOrder::generateOrderNumber(),
            'customer_id'        => $customer->id,
            'hosting_package_id' => $legacyPackageId ?? ($hostingOrder->hosting_package_id ?? ($order->hosting_package_id ?? ($package->id ?? null))),
            'invoice_id'         => $invoice->id,
            'domain'             => $validated['domain'],
            'total'              => $total,
            'status'             => 'pending',
            'notes'              => $validated['notes'] ?? null,
        ]);

        ActivityLog::log('create_hosting_order', "Created hosting order: {$order->order_number} for {$customer->name}");

        return redirect()->route('hosting-orders.index')
            ->with('success', 'Order hosting berhasil dibuat. Invoice: ' . $invoice->invoice_number);
    }

    public function show(HostingOrder $hostingOrder)
    {
        $hostingOrder->load('customer', 'hostingPackage', 'hostingAccount', 'invoice');
        return view('hosting-orders.show', compact('hostingOrder'));
    }

    public function provision(HostingOrder $hostingOrder)
    {
        if ($hostingOrder->hosting_account_id) {
            return back()->with('error', 'Order sudah punya akun hosting.');
        }

        try {
            $package  = $hostingOrder->hostingPackage;
            $customer = $hostingOrder->customer;
            $domain   = $hostingOrder->domain;
            $username = $this->generateUsername($domain);
            $password = bin2hex(random_bytes(8));

            $account = HostingAccount::create([
                'customer_id'        => $customer->id,
                'hosting_package_id' => $legacyPackageId ?? ($hostingOrder->hosting_package_id ?? ($order->hosting_package_id ?? ($package->id ?? null))),
                'domain'             => $domain,
                'username'           => $username,
                'password'           => $password,
                'server_type'        => 'whm',
                'status'             => 'pending',
                'started_at'         => now(),
                'expired_at'         => now()->addDays($package->duration_days),
            ]);

            $hostingOrder->update([
                'hosting_account_id' => $account->id,
                'status'             => 'provisioning',
            ]);

            $provisioner = HostingProvisionerFactory::make($account->server_type);
            $result = $provisioner->createAccount($account);

            if ($result['success']) {
                $account->update(['status' => 'active']);
                $hostingOrder->update([
                    'status'        => 'active',
                    'provision_log' => 'Provisioned at ' . now()->toDateTimeString() . ': ' . $result['message'],
                ]);
                ActivityLog::log('provision_hosting_order', "Provisioned order {$hostingOrder->order_number}: {$username}@{$domain}");
                return back()->with('success', 'Hosting berhasil di-provision. Username: ' . $username);
            }

            $hostingOrder->update([
                'status'        => 'failed',
                'provision_log' => 'Failed at ' . now()->toDateTimeString() . ': ' . $result['message'],
            ]);
            return back()->with('error', 'Gagal provision: ' . $result['message']);
        } catch (\Throwable $e) {
            $hostingOrder->update([
                'status'        => 'failed',
                'provision_log' => 'Error at ' . now()->toDateTimeString() . ': ' . $e->getMessage(),
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cancel(HostingOrder $hostingOrder)
    {
        $hostingOrder->update(['status' => 'cancelled']);

        if ($hostingOrder->invoice && $hostingOrder->invoice->status === 'unpaid') {
            $hostingOrder->invoice->update(['status' => 'cancelled']);
        }

        ActivityLog::log('cancel_hosting_order', "Cancelled order: {$hostingOrder->order_number}");

        return back()->with('success', 'Order hosting berhasil dibatalkan.');
    }

    protected function generateUsername(string $domain): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('.', $domain)[0]));
        $base = substr($base, 0, 8);

        $username = $base;
        $counter  = 0;

        while (HostingAccount::where('username', $username)->exists()) {
            $counter++;
            $username = $base . $counter;
        }

        return $username;
    }
    public function destroy(\App\Models\HostingOrder $hostingOrder)
{
    if (in_array($hostingOrder->status, ['active', 'provisioning'])) {
        return back()->with('error', 'Hosting order aktif/provisioning tidak bisa dihapus. Cancel dulu.');
    }

    $orderNumber = $hostingOrder->order_number ?? $hostingOrder->id;

    $hostingOrder->delete();

    \App\Models\ActivityLog::log('delete_hosting_order', "Deleted hosting order: {$orderNumber}");

    return redirect()->route('hosting-orders.index')
        ->with('success', 'Hosting order berhasil dihapus.');
  }
    public function updateStatus(\Illuminate\Http\Request $request, \App\Models\HostingOrder $hostingOrder)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,paid,provisioning,active,failed,cancelled',
    ]);

    $oldStatus = $hostingOrder->status;
    $newStatus = $validated['status'];

    $hostingOrder->update([
        'status' => $newStatus,
    ]);

    if ($hostingOrder->invoice) {
        if ($newStatus === 'paid' || $newStatus === 'active') {
            $hostingOrder->invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        if ($newStatus === 'cancelled') {
            $hostingOrder->invoice->update([
                'status' => 'cancelled',
            ]);
        }
    }

    \App\Models\ActivityLog::log(
        'update_hosting_order_status',
        "Updated hosting order {$hostingOrder->order_number} status from {$oldStatus} to {$newStatus}"
    );

    return back()->with('success', "Status hosting order berhasil diubah ke {$newStatus}.");
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



<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UnifiedOrderController extends Controller
{
    public function index()
    {
        return redirect()->route('unified-orders.create');
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $packages = Package::where('is_active', true)
            ->orderBy('service_type')
            ->orderBy('name')
            ->get();

        return view('orders.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'service_type' => strtolower(trim($request->input('service_type', ''))),
        ]);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_type' => 'required|in:vpn,hosting,vps',
            'package_id' => 'required|exists:packages,id',
            'domain' => 'nullable|string|max:255',
            'hostname' => 'nullable|string|max:255',
            'billing_cycle' => 'nullable|string|in:monthly,quarterly,yearly',
            'os_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        $package = Package::where('id', $validated['package_id'])
            ->where('service_type', $validated['service_type'])
            ->where('is_active', true)
            ->firstOrFail();

        if ($validated['service_type'] === 'hosting' && empty($validated['domain'])) {
            return back()->withInput()->with('error', 'Domain wajib diisi untuk order Hosting.');
        }

        if ($validated['service_type'] === 'vps' && empty($validated['hostname'])) {
            return back()->withInput()->with('error', 'Hostname wajib diisi untuk order VPS.');
        }

        DB::beginTransaction();

        try {
            $multiplier = match ($validated['billing_cycle'] ?? 'monthly') {
                'quarterly' => 3,
                'yearly' => 12,
                default => 1,
            };

            $price = (float) ($package->price ?? 0);
            $setupFee = (float) ($package->setup_fee ?? 0);

            $subtotal = $price * $multiplier;
            $total = $subtotal + $setupFee;

            $invoice = $this->createInvoice($customer, $package, $subtotal, $setupFee, $total, $validated);

            $orderNumber = $this->generateOrderNumber($validated['service_type']);

            if ($validated['service_type'] === 'hosting') {
                $legacyPackageId = $this->syncLegacyHostingPackage($package);

                if (Schema::hasTable('hosting_orders')) {
                    $this->insertFiltered('hosting_orders', [
                        'order_number' => $orderNumber,
                        'customer_id' => $customer->id,
                        'package_id' => $package->id,
                        'hosting_package_id' => $legacyPackageId,
                        'invoice_id' => $invoice->id,
                        'domain' => $validated['domain'],
                        'status' => 'pending',
                        'notes' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($validated['service_type'] === 'vps') {
                $legacyPackageId = $this->syncLegacyVpsPackage($package);

                if (Schema::hasTable('vps_orders')) {
                    $this->insertFiltered('vps_orders', [
                        'order_number' => $orderNumber,
                        'customer_id' => $customer->id,
                        'package_id' => $package->id,
                        'vps_package_id' => $legacyPackageId,
                        'invoice_id' => $invoice->id,
                        'hostname' => $validated['hostname'],
                        'billing_cycle' => $validated['billing_cycle'] ?? 'monthly',
                        'os_name' => $validated['os_name'] ?? 'Ubuntu 22.04',
                        'price' => $total,
                        'status' => 'pending',
                        'notes' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($validated['service_type'] === 'vpn') {
                if (Schema::hasTable('vpn_orders')) {
                    $this->insertFiltered('vpn_orders', [
                        'order_number' => $orderNumber,
                        'customer_id' => $customer->id,
                        'package_id' => $package->id,
                        'invoice_id' => $invoice->id,
                        'status' => 'pending',
                        'notes' => $validated['notes'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (class_exists(\App\Models\ActivityLog::class)) {
                \App\Models\ActivityLog::log(
                    'create_unified_order',
                    'Created ' . strtoupper($validated['service_type']) . ' order: ' . $orderNumber
                );
            }

            DB::commit();

            if (Route::has('invoices.show')) {
                return redirect()->route('invoices.show', $invoice->id)
                    ->with('success', 'Order berhasil dibuat. Invoice sudah dibuat.');
            }

            return redirect()->route('invoices.index')
                ->with('success', 'Order berhasil dibuat. Invoice sudah dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat order: ' . $e->getMessage());
        }
    }

    private function createInvoice($customer, $package, float $subtotal, float $setupFee, float $total, array $data)
    {
        $invoiceNumber = method_exists(Invoice::class, 'generateInvoiceNumber')
            ? Invoice::generateInvoiceNumber()
            : 'INV-' . now()->format('YmdHis');

        $notes = trim(($data['notes'] ?? '') . "\n\nService Type: " . strtoupper($data['service_type']));

        if (!empty($data['domain'])) {
            $notes .= "\nDomain: " . $data['domain'];
        }

        if (!empty($data['hostname'])) {
            $notes .= "\nHostname: " . $data['hostname'];
        }

        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'subtotal' => $subtotal,
            'setup_fee' => $setupFee,
            'discount' => 0,
            'tax' => 0,
            'total' => $total,
            'status' => 'unpaid',
            'due_date' => now()->addDays(7),
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $invoiceData = $this->filterColumns('invoices', $invoiceData);

        return Invoice::create($invoiceData);
    }

    private function generateOrderNumber(string $type): string
    {
        $prefix = match ($type) {
            'hosting' => 'HST',
            'vps' => 'VPS',
            default => 'VPN',
        };

        return $prefix . '-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4));
    }

    private function syncLegacyHostingPackage($package): int
    {
        if (!Schema::hasTable('hosting_packages')) {
            return (int) $package->id;
        }

        $this->upsertFiltered(
            'hosting_packages',
            ['id' => $package->id],
            [
                'id' => $package->id,
                'name' => $package->name,
                'price' => $package->price ?? 0,
                'setup_fee' => $package->setup_fee ?? 0,
                'duration_days' => $package->duration_days ?? 30,
                'disk_space_mb' => 1000,
                'bandwidth_mb' => null,
                'max_email_accounts' => max((int) ($package->max_users ?? 1), 1),
                'max_databases' => 3,
                'max_addon_domains' => 0,
                'max_parked_domains' => 0,
                'max_subdomains' => 5,
                'cpanel_package' => $package->name,
                'description' => $package->description,
                'is_active' => (bool) ($package->is_active ?? true),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) $package->id;
    }

    private function syncLegacyVpsPackage($package): int
    {
        if (!Schema::hasTable('vps_packages')) {
            return (int) $package->id;
        }

        $this->upsertFiltered(
            'vps_packages',
            ['id' => $package->id],
            [
                'id' => $package->id,
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
                'is_active' => (bool) ($package->is_active ?? true),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return (int) $package->id;
    }

    private function insertFiltered(string $table, array $data): int
    {
        $data = $this->filterColumns($table, $data);

        return DB::table($table)->insertGetId($data);
    }

    private function upsertFiltered(string $table, array $keys, array $data): void
    {
        $data = $this->filterColumns($table, $data);
        $keys = $this->filterColumns($table, $keys);

        DB::table($table)->updateOrInsert($keys, $data);
    }

    private function filterColumns(string $table, array $data): array
    {
        if (!Schema::hasTable($table)) {
            return $data;
        }

        $columns = Schema::getColumnListing($table);

        return collect($data)
            ->filter(fn ($value, $key) => in_array($key, $columns, true))
            ->toArray();
    }
}

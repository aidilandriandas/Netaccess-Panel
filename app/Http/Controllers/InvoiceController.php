<?php

namespace App\Http\Controllers;

use App\Services\ClientEmailService;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\PaymentProof;
use App\Models\Setting;
use App\Models\VpnUser;
use App\Services\MikrotikL2TPService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer', 'package', 'vpnUser');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $packages  = Package::where('is_active', true)->orderBy('name')->get();
        return view('invoices.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id'  => 'required|exists:packages,id',
            'vpn_user_id' => 'nullable|exists:vpn_users,id',
            'subtotal'    => 'required|numeric|min:0',
            'setup_fee'   => 'nullable|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0',
            'due_date'    => 'required|date',
            'notes'       => 'nullable|string|max:2000',
        ]);

        $subtotal = $validated['subtotal'];
        $setupFee = $validated['setup_fee'] ?? 0;
        $discount = $validated['discount'] ?? 0;

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id'    => $validated['customer_id'],
            'package_id'     => $validated['package_id'],
            'vpn_user_id'    => $validated['vpn_user_id'] ?? null,
            'subtotal'       => $subtotal,
            'setup_fee'      => $setupFee,
            'discount'       => $discount,
            'total'          => $subtotal + $setupFee - $discount,
            'status'         => 'unpaid',
            'due_date'       => $validated['due_date'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        ActivityLog::log('create_invoice', "Created invoice: {$invoice->invoice_number}");

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('customer', 'package', 'vpnUser', 'paymentProofs');

        // URL WA manual untuk invoice yang sudah paid (tombol "Kirim WA" ulang)
        $waManualUrl = null;
        if ($invoice->status === 'paid' && $invoice->customer->phone && $invoice->vpnUser) {
            $waManualUrl = $this->buildWaUrl(
                $invoice->customer,
                $invoice->vpnUser,
                $invoice,
                false // bukan new user, teks perpanjangan
            );
        }

        return view('invoices.show', compact('invoice', 'waManualUrl'));
    }


public function markPaid(\App\Models\Invoice $invoice)
{
    $alreadyPaid = $invoice->status === 'paid';

    $invoice->load(['customer', 'package', 'vpnUser']);

    if (!$alreadyPaid) {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    } elseif (\Illuminate\Support\Facades\Schema::hasColumn('invoices', 'paid_at') && empty($invoice->paid_at)) {
        $invoice->update([
            'paid_at' => now(),
        ]);
    }

    \App\Models\ActivityLog::log(
        'mark_invoice_paid',
        "Marked invoice as paid: {$invoice->invoice_number}"
    );

    $package = $invoice->package;
    $customer = $invoice->customer;

    if (!$package) {
        $this->sendServiceActivatedEmail($invoice);
        return back()->with('success', 'Invoice berhasil ditandai paid, tapi package tidak ditemukan.');
    }

    if (!$customer) {
        $this->sendServiceActivatedEmail($invoice);
        return back()->with('success', 'Invoice paid, tapi customer tidak ditemukan.');
    }

    $serviceType = strtolower((string) ($package->service_type ?? 'vpn'));

    try {
        if ($serviceType === 'hosting') {
            $result = $this->createHostingServiceFromInvoice($invoice, $package, $customer);
            $this->sendServiceActivatedEmail($invoice);

            return back()->with('success', $result);
        }

        if ($serviceType === 'vps') {
            $result = $this->createVpsServiceFromInvoice($invoice, $package, $customer);
            $this->sendServiceActivatedEmail($invoice);

            return back()->with('success', $result);
        }

        if ($serviceType !== 'vpn') {
            $this->sendServiceActivatedEmail($invoice);
            return back()->with('success', 'Invoice berhasil ditandai paid untuk layanan ' . strtoupper($serviceType) . '.');
        }

        $vpnUser = $invoice->vpnUser;

        if (!$vpnUser) {
            $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', $customer->name));
            $baseUsername = substr($baseUsername, 0, 10);

            if (!$baseUsername) {
                $baseUsername = 'vpnuser';
            }

            $username = $baseUsername;
            $counter = 1;

            while (\App\Models\VpnUser::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $password = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 10);

            $mikrotik = app(\App\Services\MikrotikL2TPService::class);

            $assignedIp = null;
            if (method_exists($mikrotik, 'getNextAvailableIp')) {
                $assignedIp = $mikrotik->getNextAvailableIp();
            }

            $vpnData = [
                'customer_id' => $customer->id,
                'package_id'  => $package->id,
                'invoice_id'  => $invoice->id,
                'username'    => $username,
                'status'      => 'active',
                'expired_at'  => now()->addDays((int) ($package->duration_days ?? 30)),
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('vpn_users', 'l2tp_password')) {
                $vpnData['l2tp_password'] = $password;
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('vpn_users', 'password')) {
                $vpnData['password'] = $password;
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('vpn_users', 'assigned_ip') && $assignedIp) {
                $vpnData['assigned_ip'] = $assignedIp;
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('vpn_users', 'remote_address') && $assignedIp) {
                $vpnData['remote_address'] = $assignedIp;
            }

            $vpnUser = \App\Models\VpnUser::create($vpnData);
        } else {
            $mikrotik = app(\App\Services\MikrotikL2TPService::class);
        }

        $result = $mikrotik->createUser($vpnUser);

        if (is_array($result) && ($result['success'] ?? false)) {
            $vpnUser->update([
                'status' => 'active',
            ]);

            $this->sendServiceActivatedEmail($invoice);

            return back()->with('success', 'Invoice paid dan user VPN berhasil dibuat ke MikroTik.');
        }

        $message = is_array($result)
            ? ($result['message'] ?? 'Unknown error')
            : 'Unknown error';

        \Log::error('VPN provisioning failed after invoice paid', [
            'invoice_id' => $invoice->id,
            'vpn_user_id' => $vpnUser->id ?? null,
            'message' => $message,
            'result' => $result,
        ]);

        if ($vpnUser && $vpnUser->invoice_id === $invoice->id) {
            $vpnUser->delete();
        }

        $this->sendServiceActivatedEmail($invoice);

        return back()->with('success', 'Invoice paid, tapi provisioning VPN gagal: ' . $message);
    } catch (\Throwable $e) {
        \Log::error('Invoice paid service provisioning exception', [
            'invoice_id' => $invoice->id,
            'service_type' => $serviceType,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        $this->sendServiceActivatedEmail($invoice);

        return back()->with('success', 'Invoice paid, tapi auto create layanan error: ' . $e->getMessage());
    }
}

    public function markUnpaid(Invoice $invoice)
    {
        $invoice->update([
            'status'  => 'unpaid',
            'paid_at' => null,
        ]);

        ActivityLog::log('invoice_unpaid', "Invoice marked as unpaid: {$invoice->invoice_number}");

        return back()->with('success', 'Invoice ditandai belum lunas.');
    }

    public function cancel(Invoice $invoice)
    {
        $invoice->update(['status' => 'cancelled']);
        ActivityLog::log('invoice_cancelled', "Invoice cancelled: {$invoice->invoice_number}");

        return back()->with('success', 'Invoice dibatalkan.');
    }

    public function uploadProof(Request $request, Invoice $invoice)
    {
        $request->validate([
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'      => 'nullable|string|max:500',
        ]);

        $file = $request->file('proof_file');
        $path = $file->store('payment-proofs', 'public');

        PaymentProof::create([
            'invoice_id' => $invoice->id,
            'file_path'  => $path,
            'file_name'  => $file->getClientOriginalName(),
            'file_type'  => $file->getClientMimeType(),
            'notes'      => $request->notes,
        ]);

        ActivityLog::log('upload_payment_proof', "Payment proof uploaded for: {$invoice->invoice_number}");

        return back()->with('success', 'Bukti pembayaran berhasil diupload.');
    }

    public function destroy(Invoice $invoice)
    {
        $number = $invoice->invoice_number;
        $invoice->delete();
        ActivityLog::log('delete_invoice', "Deleted invoice: {$number}");

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dihapus.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function buildWaUrl(
        Customer $customer,
        VpnUser $vpnUser,
        Invoice $invoice,
        bool $isNewUser
    ): string {
        $businessName = Setting::get('business_name', 'NetAccess');
        $server       = Setting::get('mikrotik_public_ip', Setting::get('mikrotik_host', ''));
        $ipsecSecret  = Setting::get('l2tp_ipsec_secret', '');

        if ($isNewUser) {
            $message = "Halo {$customer->name},\n\n"
                . "Pembayaran invoice *{$invoice->invoice_number}* telah dikonfirmasi ✅\n\n"
                . "Berikut detail akses VPN L2TP Anda:\n"
                . "━━━━━━━━━━━━━━━━━\n"
                . "🌐 *Server*: {$server}\n"
                . "👤 *Username*: {$vpnUser->username}\n"
                . "🔑 *Password*: {$vpnUser->l2tp_password}\n"
                . "🔐 *IPSec Secret*: {$ipsecSecret}\n"
                . "📦 *Paket*: {$vpnUser->package->name}\n"
                . "📅 *Aktif s/d*: {$vpnUser->expired_at->format('d M Y')}\n"
                . "━━━━━━━━━━━━━━━━━\n\n"
                . "Panduan koneksi:\n"
                . "• *Windows*: Settings → VPN → Add VPN → L2TP/IPsec PSK\n"
                . "• *Android*: Settings → VPN → Add → L2TP/IPSec PSK\n"
                . "• *iOS*: Settings → VPN → Add Configuration → L2TP\n\n"
                . "Hubungi kami jika ada kendala.\n"
                . "Terima kasih 🙏 - {$businessName}";
        } else {
            $message = "Halo {$customer->name},\n\n"
                . "Pembayaran invoice *{$invoice->invoice_number}* telah dikonfirmasi ✅\n\n"
                . "Masa aktif VPN Anda telah diperpanjang:\n"
                . "━━━━━━━━━━━━━━━━━\n"
                . "👤 *Username*: {$vpnUser->username}\n"
                . "📦 *Paket*: {$vpnUser->package->name}\n"
                . "📅 *Aktif s/d*: {$vpnUser->expired_at->format('d M Y')}\n"
                . "━━━━━━━━━━━━━━━━━\n\n"
                . "Terima kasih atas kepercayaan Anda 🙏 - {$businessName}";
        }

        $phone = preg_replace('/[^0-9]/', '', $customer->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
    }


    private function createHostingServiceFromInvoice($invoice, $package, $customer): string
    {
        if (!Schema::hasTable('hosting_accounts')) {
            return 'Invoice paid. Tabel hosting_accounts belum ada.';
        }

        $order = Schema::hasTable('hosting_orders')
            ? DB::table('hosting_orders')->where('invoice_id', $invoice->id)->first()
            : null;

        if (!$order) {
            return 'Invoice paid. Hosting account belum dibuat karena invoice ini bukan dari Hosting Order.';
        }

        if (!empty($order->hosting_account_id) && DB::table('hosting_accounts')->where('id', $order->hosting_account_id)->exists()) {
            DB::table('hosting_orders')->where('id', $order->id)->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);

            return 'Invoice paid. Hosting account sudah ada.';
        }

        $legacyPackageId = $this->syncLegacyHostingPackage($package);

        $domain = $order->domain ?: ('hosting-' . $invoice->id . '.local');
        $username = $this->generateHostingUsername($domain, $customer);
        $password = Str::password(12);

        $data = [
            'customer_id' => $customer->id,
            'hosting_package_id' => $legacyPackageId,
            'domain' => $domain,
            'username' => $username,
            'password' => $password,
            'server_type' => 'whm',
            'status' => 'pending',
            'started_at' => now(),
            'expired_at' => now()->addDays((int) ($package->duration_days ?? 30)),
            'notes' => 'Auto created from paid invoice ' . $invoice->invoice_number,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('hosting_accounts', 'package_id')) {
            $data['package_id'] = $package->id;
        }

        $hostingAccountId = DB::table('hosting_accounts')->insertGetId($data);

        DB::table('hosting_orders')->where('id', $order->id)->update([
            'hosting_account_id' => $hostingAccountId,
            'status' => 'paid',
            'updated_at' => now(),
        ]);

        ActivityLog::log('auto_create_hosting_from_invoice', "Auto created hosting account from invoice: {$invoice->invoice_number}");

        return 'Invoice paid dan Hosting Account berhasil dibuat pending. Lanjut klik Provision untuk buat akun di WHM.';
    }

    private function createVpsServiceFromInvoice($invoice, $package, $customer): string
    {
        if (!Schema::hasTable('vps_services')) {
            return 'Invoice paid. Tabel vps_services belum ada.';
        }

        $order = Schema::hasTable('vps_orders')
            ? DB::table('vps_orders')->where('invoice_id', $invoice->id)->first()
            : null;

        if (!$order) {
            return 'Invoice paid. VPS service belum dibuat karena invoice ini bukan dari VPS Order.';
        }

        if (!empty($order->vps_service_id) && DB::table('vps_services')->where('id', $order->vps_service_id)->exists()) {
            DB::table('vps_orders')->where('id', $order->id)->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);

            return 'Invoice paid. VPS service sudah ada.';
        }

        $legacyPackageId = $this->syncLegacyVpsPackage($package);
        $legacyPackage = Schema::hasTable('vps_packages')
            ? DB::table('vps_packages')->where('id', $legacyPackageId)->first()
            : null;

        $data = [
            'customer_id' => $customer->id,
            'vps_package_id' => $legacyPackageId,
            'vps_server_id' => null,
            'hostname' => $order->hostname ?: ('vps-' . $invoice->id),
            'main_ip' => null,
            'username' => 'root',
            'password' => null,
            'ssh_port' => 22,
            'os_name' => $order->os_name ?: 'Ubuntu 22.04',
            'cpu_cores' => (int) ($legacyPackage->cpu_cores ?? 1),
            'ram_mb' => (int) ($legacyPackage->ram_mb ?? 1024),
            'storage_gb' => (int) ($legacyPackage->storage_gb ?? 20),
            'bandwidth_gb' => $legacyPackage->bandwidth_gb ?? null,
            'status' => 'pending',
            'started_at' => now(),
            'expired_at' => now()->addDays((int) ($package->duration_days ?? 30)),
            'notes' => 'Auto created from paid invoice ' . $invoice->invoice_number,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('vps_services', 'package_id')) {
            $data['package_id'] = $package->id;
        }

        $vpsServiceId = DB::table('vps_services')->insertGetId($data);

        DB::table('vps_orders')->where('id', $order->id)->update([
            'vps_service_id' => $vpsServiceId,
            'status' => 'paid',
            'updated_at' => now(),
        ]);

        ActivityLog::log('auto_create_vps_from_invoice', "Auto created VPS service from invoice: {$invoice->invoice_number}");

        return 'Invoice paid dan VPS Service berhasil dibuat pending. Lengkapi IP/server lalu provision manual.';
    }

    private function syncLegacyHostingPackage($package): int
    {
        if (!Schema::hasTable('hosting_packages')) {
            return $package->id;
        }

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
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $package->id;
    }

    private function syncLegacyVpsPackage($package): int
    {
        if (!Schema::hasTable('vps_packages')) {
            return $package->id;
        }

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
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $package->id;
    }

    private function generateHostingUsername(string $domain, $customer): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('.', $domain)[0] ?: $customer->name));
        $base = substr($base ?: 'hosting', 0, 10);

        $username = $base;
        $counter = 1;

        while (DB::table('hosting_accounts')->where('username', $username)->exists()) {
            $suffix = (string) $counter;
            $username = substr($base, 0, max(1, 16 - strlen($suffix))) . $suffix;
            $counter++;
        }

        return $username;
    }


    private function sendServiceActivatedEmail($invoice): void
    {
        try {
            app(ClientEmailService::class)->sendServiceActivated($invoice);
        } catch (\Throwable $e) {
            \Log::error('Gagal kirim email aktivasi layanan: ' . $e->getMessage());
        }
    }

}

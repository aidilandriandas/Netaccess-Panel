<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\HostingPackage;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use App\Models\VpsPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Admin Owner
        User::create([
            'name'     => 'Admin Owner',
            'email'    => 'admin@netaccess.local',
            'password' => Hash::make('password'),
            'role'     => 'owner',
        ]);

        // Default Client user (linked to a customer)
        $demoCustomer = Customer::create([
            'name'          => 'Demo Client',
            'phone'         => '6281000000000',
            'email'         => 'client@netaccess.local',
            'customer_type' => 'personal',
            'status'        => 'active',
        ]);

        User::create([
            'name'        => 'Demo Client',
            'email'       => 'client@netaccess.local',
            'password'    => Hash::make('password'),
            'role'        => 'client',
            'customer_id' => $demoCustomer->id,
        ]);

        // Default Packages
        $packages = [
            ['name' => 'VPN CCTV Basic',  'service_type' => 'vpn',        'price' => 50000,  'setup_fee' => 25000, 'duration_days' => 30, 'max_users' => 1, 'description' => 'Paket VPN L2TP dasar untuk remote CCTV, 1 user.'],
            ['name' => 'VPN Personal',     'service_type' => 'vpn',        'price' => 75000,  'setup_fee' => 0,     'duration_days' => 30, 'max_users' => 1, 'description' => 'Paket VPN L2TP personal untuk akses remote.'],
            ['name' => 'VPN Kantor',       'service_type' => 'vpn',        'price' => 150000, 'setup_fee' => 50000, 'duration_days' => 30, 'max_users' => 5, 'description' => 'Paket VPN L2TP kantor, hingga 5 user.'],
            ['name' => 'Monitoring Basic', 'service_type' => 'monitoring', 'price' => 100000, 'setup_fee' => 0,     'duration_days' => 30, 'max_users' => 1, 'description' => 'Monitoring server basic.'],
            ['name' => 'Hosting Basic',    'service_type' => 'hosting',    'price' => 50000,  'setup_fee' => 0,     'duration_days' => 30, 'max_users' => 1, 'description' => 'Shared hosting basic.'],
        ];

        foreach ($packages as $pkg) {
            Package::create($pkg);
        }

        // Default Hosting Packages
        $hostingPackages = [
            ['name' => 'Starter Hosting', 'price' => 50000, 'setup_fee' => 0, 'duration_days' => 30, 'disk_space_mb' => 1000, 'bandwidth_mb' => 10000, 'max_email_accounts' => 5, 'max_databases' => 3, 'max_addon_domains' => 0, 'max_subdomains' => 5, 'cpanel_package' => 'starter', 'description' => 'Paket hosting pemula, cocok untuk website personal.'],
            ['name' => 'Business Hosting', 'price' => 150000, 'setup_fee' => 25000, 'duration_days' => 30, 'disk_space_mb' => 5000, 'bandwidth_mb' => 50000, 'max_email_accounts' => 20, 'max_databases' => 10, 'max_addon_domains' => 3, 'max_subdomains' => 20, 'cpanel_package' => 'business', 'description' => 'Paket hosting bisnis untuk company profile & toko online.'],
            ['name' => 'Premium Hosting', 'price' => 300000, 'setup_fee' => 50000, 'duration_days' => 30, 'disk_space_mb' => 20000, 'bandwidth_mb' => 100000, 'max_email_accounts' => 50, 'max_databases' => 25, 'max_addon_domains' => 10, 'max_subdomains' => 50, 'cpanel_package' => 'premium', 'description' => 'Paket hosting premium untuk traffic tinggi.'],
        ];

        foreach ($hostingPackages as $pkg) {
            HostingPackage::create($pkg);
        }

        // Default Settings
        $settings = [
            // Business
            'business_name'           => 'NetAccess',
            'whatsapp_number'         => '6281234567890',
            // Payment
            'payment_bank_name'       => 'BCA',
            'payment_account_number'  => '1234567890',
            'payment_account_name'    => 'NetAccess',
            'invoice_footer'          => 'Terima kasih atas kepercayaan Anda. Pembayaran dapat dilakukan via transfer bank atau QRIS.',
            // Mikrotik L2TP
            'mikrotik_host'           => '',
            'mikrotik_public_ip'      => '',
            'mikrotik_user'           => 'admin',
            'mikrotik_password'       => '',
            'mikrotik_api_port'       => '8728',
            'l2tp_ipsec_secret'       => 'rahasia',
            'l2tp_ip_pool'            => '192.168.100.0/24',
            'l2tp_local_address'      => '192.168.100.1',
            'l2tp_profile'            => 'default-encryption',
            // WHM/cPanel
            'whm_host'                => '',
            'whm_api_token'           => '',
            'whm_port'                => '2087',
            // DirectAdmin
            'directadmin_host'        => '',
            'directadmin_user'        => 'admin',
            'directadmin_password'    => '',
            'directadmin_port'        => '2222',
            // VPS
            'vps_default_billing_cycle'        => 'monthly',
            'vps_default_ssh_port'             => '22',
            'vps_enable_auto_provisioning'     => 'false',
            'vps_default_provider'             => 'manual',
            'vps_client_can_request_reinstall' => 'false',
            'vps_client_can_reboot'            => 'false',
            'vps_client_can_view_password'     => 'false',
            // WhatsApp Templates
            'whatsapp_template_h7'      => "Halo {name}, layanan {package_name} Anda akan expired pada {expired_date}.\nTotal tagihan: Rp {total}.\nSilakan lakukan pembayaran agar layanan tetap aktif.\nTerima kasih. - {business_name}",
            'whatsapp_template_h3'      => "Halo {name}, layanan {package_name} Anda akan expired 3 hari lagi ({expired_date}).\nTotal tagihan: Rp {total}.\nSegera lakukan pembayaran.\nTerima kasih. - {business_name}",
            'whatsapp_template_h1'      => "Halo {name}, layanan {package_name} Anda akan expired BESOK ({expired_date}).\nTotal tagihan: Rp {total}.\nSegera lakukan pembayaran agar layanan tidak terputus.\nTerima kasih. - {business_name}",
            'whatsapp_template_expired' => "Halo {name}, layanan {package_name} Anda sudah EXPIRED pada {expired_date}.\nTotal tagihan: Rp {total}.\nLayanan akan di-suspend jika tidak segera dibayar.\nTerima kasih. - {business_name}",
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        // VPS Packages
        VpsPackage::create([
            'name'          => 'VPS Starter',
            'description'   => 'VPS dasar untuk development atau hosting ringan.',
            'price'         => 50000,
            'billing_cycle' => 'monthly',
            'cpu_cores'     => 1,
            'ram_mb'        => 1024,
            'storage_gb'    => 20,
            'bandwidth_gb'  => null,
            'ipv4_count'    => 1,
            'os_options'    => ['Ubuntu 22.04', 'Ubuntu 24.04', 'Debian 12', 'CentOS 9 Stream'],
            'is_active'     => true,
        ]);

        VpsPackage::create([
            'name'          => 'VPS Basic',
            'description'   => 'VPS untuk website dan aplikasi menengah.',
            'price'         => 100000,
            'billing_cycle' => 'monthly',
            'cpu_cores'     => 2,
            'ram_mb'        => 2048,
            'storage_gb'    => 40,
            'bandwidth_gb'  => null,
            'ipv4_count'    => 1,
            'os_options'    => ['Ubuntu 22.04', 'Ubuntu 24.04', 'Debian 12', 'CentOS 9 Stream', 'Windows Server 2022'],
            'is_active'     => true,
        ]);
    }
}

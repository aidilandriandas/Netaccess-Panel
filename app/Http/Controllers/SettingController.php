<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\Hosting\HostingProvisionerFactory;
use App\Services\MikrotikL2TPService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected array $settingKeys = [
        'business_name', 'business_logo', 'whatsapp_number',
        'payment_bank_name', 'payment_account_number', 'payment_account_name',
        'qris_image', 'invoice_footer',
        'whatsapp_template_h7', 'whatsapp_template_h3',
        'whatsapp_template_h1', 'whatsapp_template_expired',
        // Mikrotik L2TP
        'mikrotik_host', 'mikrotik_public_ip', 'mikrotik_user',
        'mikrotik_password', 'mikrotik_api_port',
        'l2tp_ipsec_secret', 'l2tp_ip_pool', 'l2tp_local_address', 'l2tp_profile',
        // WHM/cPanel
        'whm_host', 'whm_api_token', 'whm_port',
        // DirectAdmin
        'directadmin_host', 'directadmin_user', 'directadmin_password', 'directadmin_port',
        // VPS
        'vps_default_billing_cycle', 'vps_default_ssh_port', 'vps_enable_auto_provisioning',
        'vps_default_provider', 'vps_client_can_request_reinstall', 'vps_client_can_reboot',
        'vps_client_can_view_password',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->settingKeys as $key) {
            $settings[$key] = Setting::get($key, '');
        }
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'business_name'            => 'nullable|string|max:255',
            'whatsapp_number'          => 'nullable|string|max:20',
            'payment_bank_name'        => 'nullable|string|max:255',
            'payment_account_number'   => 'nullable|string|max:50',
            'payment_account_name'     => 'nullable|string|max:255',
            'invoice_footer'           => 'nullable|string|max:2000',
            'whatsapp_template_h7'     => 'nullable|string|max:2000',
            'whatsapp_template_h3'     => 'nullable|string|max:2000',
            'whatsapp_template_h1'     => 'nullable|string|max:2000',
            'whatsapp_template_expired'=> 'nullable|string|max:2000',
            'mikrotik_host'            => 'nullable|string|max:255',
            'mikrotik_public_ip'       => 'nullable|string|max:255',
            'mikrotik_user'            => 'nullable|string|max:100',
            'mikrotik_password'        => 'nullable|string|max:255',
            'mikrotik_api_port'        => 'nullable|integer|min:1|max:65535',
            'l2tp_ipsec_secret'        => 'nullable|string|max:255',
            'l2tp_ip_pool'             => 'nullable|string|max:50',
            'l2tp_local_address'       => 'nullable|ip',
            'l2tp_profile'             => 'nullable|string|max:100',
            'business_logo'            => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'qris_image'               => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            // WHM/cPanel
            'whm_host'                 => 'nullable|string|max:255',
            'whm_api_token'            => 'nullable|string|max:1000',
            'whm_port'                 => 'nullable|integer|min:1|max:65535',
            // DirectAdmin
            'directadmin_host'         => 'nullable|string|max:255',
            'directadmin_user'         => 'nullable|string|max:100',
            'directadmin_password'     => 'nullable|string|max:255',
            'directadmin_port'         => 'nullable|integer|min:1|max:65535',
            // VPS
            'vps_default_billing_cycle'       => 'nullable|in:monthly,quarterly,yearly',
            'vps_default_ssh_port'            => 'nullable|integer|min:1|max:65535',
            'vps_enable_auto_provisioning'    => 'nullable|in:true,false',
            'vps_default_provider'            => 'nullable|in:manual,proxmox,virtualizor',
            'vps_client_can_request_reinstall'=> 'nullable|in:true,false',
            'vps_client_can_reboot'           => 'nullable|in:true,false',
            'vps_client_can_view_password'    => 'nullable|in:true,false',
        ]);

        foreach ($this->settingKeys as $key) {
            if ($key === 'business_logo' && $request->hasFile('business_logo')) {
                $path = $request->file('business_logo')->store('settings', 'public');
                Setting::set($key, $path);
            } elseif ($key === 'qris_image' && $request->hasFile('qris_image')) {
                $path = $request->file('qris_image')->store('settings', 'public');
                Setting::set($key, $path);
            } elseif ($request->has($key) && !in_array($key, ['business_logo', 'qris_image'])) {
                Setting::set($key, $request->input($key, ''));
            }
        }

        ActivityLog::log('update_settings', 'Settings updated');

        return back()->with('success', 'Settings berhasil disimpan.');
    }

    public function testMikrotik()
    {
        $service = app(MikrotikL2TPService::class);
        $result  = $service->testConnection();

        return response()->json($result);
    }

    public function testWhm()
    {
        try {
            $provisioner = HostingProvisionerFactory::make('whm');
            $result = $provisioner->testConnection();
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

<?php

namespace App\Services\Vps;

use App\Models\Setting;

class VpsProvisioningService
{
    public static function make(?string $providerType = null): VpsProviderInterface
    {
        $providerType = $providerType ?? Setting::get('vps_default_provider', 'manual');

        return match ($providerType) {
            'proxmox' => new ProxmoxVpsService(),
            'virtualizor' => new VirtualizorVpsService(),
            default => new ManualVpsService(),
        };
    }
}

<?php

namespace App\Services\Vps;

use App\Models\VpsService;
use Illuminate\Support\Facades\Log;

class ProxmoxVpsService implements VpsProviderInterface
{
    public function createInstance(array $data): array
    {
        Log::info('ProxmoxVpsService: createInstance (not implemented)', $data);
        return ['success' => false, 'message' => 'Proxmox auto-provisioning belum diimplementasi.'];
    }

    public function startInstance(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: startInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox start belum diimplementasi.'];
    }

    public function stopInstance(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: stopInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox stop belum diimplementasi.'];
    }

    public function rebootInstance(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: rebootInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox reboot belum diimplementasi.'];
    }

    public function suspendInstance(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: suspendInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox suspend belum diimplementasi.'];
    }

    public function unsuspendInstance(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: unsuspendInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox unsuspend belum diimplementasi.'];
    }

    public function terminateInstance(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: terminateInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox terminate belum diimplementasi.'];
    }

    public function reinstallInstance(VpsService $service, string $os): array
    {
        Log::info("ProxmoxVpsService: reinstallInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox reinstall belum diimplementasi.'];
    }

    public function getInstanceStatus(VpsService $service): array
    {
        Log::info("ProxmoxVpsService: getInstanceStatus VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Proxmox status check belum diimplementasi.'];
    }
}

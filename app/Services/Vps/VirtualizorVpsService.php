<?php

namespace App\Services\Vps;

use App\Models\VpsService;
use Illuminate\Support\Facades\Log;

class VirtualizorVpsService implements VpsProviderInterface
{
    public function createInstance(array $data): array
    {
        Log::info('VirtualizorVpsService: createInstance (not implemented)', $data);
        return ['success' => false, 'message' => 'Virtualizor auto-provisioning belum diimplementasi.'];
    }

    public function startInstance(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: startInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor start belum diimplementasi.'];
    }

    public function stopInstance(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: stopInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor stop belum diimplementasi.'];
    }

    public function rebootInstance(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: rebootInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor reboot belum diimplementasi.'];
    }

    public function suspendInstance(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: suspendInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor suspend belum diimplementasi.'];
    }

    public function unsuspendInstance(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: unsuspendInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor unsuspend belum diimplementasi.'];
    }

    public function terminateInstance(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: terminateInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor terminate belum diimplementasi.'];
    }

    public function reinstallInstance(VpsService $service, string $os): array
    {
        Log::info("VirtualizorVpsService: reinstallInstance VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor reinstall belum diimplementasi.'];
    }

    public function getInstanceStatus(VpsService $service): array
    {
        Log::info("VirtualizorVpsService: getInstanceStatus VPS #{$service->id} (not implemented)");
        return ['success' => false, 'message' => 'Virtualizor status check belum diimplementasi.'];
    }
}

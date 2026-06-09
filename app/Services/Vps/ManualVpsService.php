<?php

namespace App\Services\Vps;

use App\Models\VpsService;
use Illuminate\Support\Facades\Log;

class ManualVpsService implements VpsProviderInterface
{
    public function createInstance(array $data): array
    {
        Log::info('ManualVpsService: createInstance', $data);

        return [
            'success' => true,
            'message' => 'VPS instance created (manual provisioning). Admin harus setup server secara manual.',
        ];
    }

    public function startInstance(VpsService $service): array
    {
        Log::info("ManualVpsService: startInstance VPS #{$service->id}");

        return [
            'success' => true,
            'message' => 'Start command logged. Admin harus start VPS secara manual.',
        ];
    }

    public function stopInstance(VpsService $service): array
    {
        Log::info("ManualVpsService: stopInstance VPS #{$service->id}");

        return [
            'success' => true,
            'message' => 'Stop command logged. Admin harus stop VPS secara manual.',
        ];
    }

    public function rebootInstance(VpsService $service): array
    {
        Log::info("ManualVpsService: rebootInstance VPS #{$service->id}");

        return [
            'success' => true,
            'message' => 'Reboot command logged. Admin harus reboot VPS secara manual.',
        ];
    }

    public function suspendInstance(VpsService $service): array
    {
        Log::info("ManualVpsService: suspendInstance VPS #{$service->id}");

        $service->update(['status' => 'suspended']);

        return [
            'success' => true,
            'message' => 'VPS suspended. Admin harus suspend akses di server secara manual.',
        ];
    }

    public function unsuspendInstance(VpsService $service): array
    {
        Log::info("ManualVpsService: unsuspendInstance VPS #{$service->id}");

        $service->update(['status' => 'active']);

        return [
            'success' => true,
            'message' => 'VPS unsuspended. Admin harus aktifkan kembali akses di server secara manual.',
        ];
    }

    public function terminateInstance(VpsService $service): array
    {
        Log::info("ManualVpsService: terminateInstance VPS #{$service->id}");

        $service->update(['status' => 'terminated']);

        return [
            'success' => true,
            'message' => 'VPS terminated. Admin harus hapus VPS di server secara manual.',
        ];
    }

    public function reinstallInstance(VpsService $service, string $os): array
    {
        Log::info("ManualVpsService: reinstallInstance VPS #{$service->id} OS: {$os}");

        return [
            'success' => true,
            'message' => "Reinstall request logged (OS: {$os}). Admin harus reinstall VPS secara manual.",
        ];
    }

    public function getInstanceStatus(VpsService $service): array
    {
        return [
            'success' => true,
            'status' => $service->status,
            'message' => 'Status dari database (manual provisioning).',
        ];
    }
}

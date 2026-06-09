<?php

namespace App\Services\Vps;

use App\Models\VpsService;

interface VpsProviderInterface
{
    public function createInstance(array $data): array;

    public function startInstance(VpsService $service): array;

    public function stopInstance(VpsService $service): array;

    public function rebootInstance(VpsService $service): array;

    public function suspendInstance(VpsService $service): array;

    public function unsuspendInstance(VpsService $service): array;

    public function terminateInstance(VpsService $service): array;

    public function reinstallInstance(VpsService $service, string $os): array;

    public function getInstanceStatus(VpsService $service): array;
}

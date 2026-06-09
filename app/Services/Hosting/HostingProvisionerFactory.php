<?php

namespace App\Services\Hosting;

class HostingProvisionerFactory
{
    public static function make(string $serverType = 'whm'): HostingProvisionerInterface
    {
        return match ($serverType) {
            'directadmin' => new DirectAdminProvisioner(),
            default       => new WhmProvisioner(),
        };
    }
}

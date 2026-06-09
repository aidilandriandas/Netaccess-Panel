<?php

namespace App\Services\Hosting;

use App\Models\HostingAccount;

interface HostingProvisionerInterface
{
    public function createAccount(HostingAccount $account): array;

    public function suspendAccount(HostingAccount $account): array;

    public function unsuspendAccount(HostingAccount $account): array;

    public function terminateAccount(HostingAccount $account): array;

    public function testConnection(): array;
}

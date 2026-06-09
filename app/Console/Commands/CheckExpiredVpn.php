<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\VpnUser;
use App\Services\MikrotikL2TPService;
use Illuminate\Console\Command;

class CheckExpiredVpn extends Command
{
    protected $signature   = 'vpn:check-expired';
    protected $description = 'Check and suspend expired L2TP VPN users at Mikrotik';

    public function handle(MikrotikL2TPService $l2tpService): int
    {
        $expiredUsers = VpnUser::where('status', 'active')
            ->where('expired_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredUsers as $vpnUser) {
            $vpnUser->update(['status' => 'expired']);
            $l2tpService->suspendUser($vpnUser);

            // Update customer status jika semua VPN user expired
            $customer = $vpnUser->customer;
            if ($customer->vpnUsers()->where('status', 'active')->count() === 0) {
                $customer->update(['status' => 'expired']);
            }

            ActivityLog::create([
                'user_id'     => null,
                'action'      => 'auto_expire_vpn',
                'description' => "Auto expired L2TP user: {$vpnUser->username}",
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Scheduler',
            ]);

            $count++;
        }

        $this->info("Processed {$count} expired VPN users.");
        return self::SUCCESS;
    }
}

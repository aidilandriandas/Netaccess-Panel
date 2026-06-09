<?php

namespace App\Services;

class ServerMonitorService
{
    protected bool $isLinux;

    public function __construct()
    {
        $this->isLinux = PHP_OS_FAMILY === 'Linux';
    }

    public function getCpuUsage(): array
    {
        if (!$this->isLinux) {
            return ['percentage' => 25.5, 'note' => 'Dummy data (non-Linux environment)'];
        }

        $load = sys_getloadavg();
        $cores = (int) shell_exec('nproc 2>/dev/null') ?: 1;
        $percentage = round(($load[0] / $cores) * 100, 1);

        return ['percentage' => min($percentage, 100), 'cores' => $cores];
    }

    public function getRamUsage(): array
    {
        if (!$this->isLinux) {
            return ['total' => '4096 MB', 'used' => '2048 MB', 'free' => '2048 MB', 'percentage' => 50, 'note' => 'Dummy data'];
        }

        $output = shell_exec('free -m 2>/dev/null');
        if (!$output) {
            return ['total' => 'N/A', 'used' => 'N/A', 'free' => 'N/A', 'percentage' => 0];
        }

        preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $output, $matches);
        $total = (int) ($matches[1] ?? 0);
        $used = (int) ($matches[2] ?? 0);
        $free = (int) ($matches[3] ?? 0);
        $percentage = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        return [
            'total' => $total . ' MB',
            'used' => $used . ' MB',
            'free' => $free . ' MB',
            'percentage' => $percentage,
        ];
    }

    public function getDiskUsage(): array
    {
        if (!$this->isLinux) {
            return ['total' => '50 GB', 'used' => '20 GB', 'available' => '30 GB', 'percentage' => 40, 'note' => 'Dummy data'];
        }

        $output = shell_exec("df -h / 2>/dev/null | tail -1");
        if (!$output) {
            return ['total' => 'N/A', 'used' => 'N/A', 'available' => 'N/A', 'percentage' => 0];
        }

        $parts = preg_split('/\s+/', trim($output));

        return [
            'total' => $parts[1] ?? 'N/A',
            'used' => $parts[2] ?? 'N/A',
            'available' => $parts[3] ?? 'N/A',
            'percentage' => (int) str_replace('%', '', $parts[4] ?? '0'),
        ];
    }

    public function getUptime(): string
    {
        if (!$this->isLinux) {
            return '15 days, 3:42 (dummy data)';
        }

        return trim(shell_exec('uptime -p 2>/dev/null') ?? 'N/A');
    }

    public function getLoadAverage(): array
    {
        $load = sys_getloadavg();
        return [
            '1min' => round($load[0], 2),
            '5min' => round($load[1], 2),
            '15min' => round($load[2], 2),
        ];
    }

    public function getServiceStatus(string $service): string
    {
        if (!$this->isLinux) {
            return 'unknown';
        }

        $result = trim(shell_exec("systemctl is-active {$service} 2>/dev/null") ?? 'unknown');
        return $result;
    }

    public function getAllServiceStatuses(): array
    {
        $services = [
            'nginx', 'mysql', 'mariadb', 'php-fpm',
            'wg-quick@wg0', 'supervisor', 'cron',
        ];

        $statuses = [];
        foreach ($services as $service) {
            $statuses[$service] = $this->getServiceStatus($service);
        }

        return $statuses;
    }

    public function getFullReport(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'ram' => $this->getRamUsage(),
            'disk' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'load_average' => $this->getLoadAverage(),
            'services' => $this->getAllServiceStatuses(),
        ];
    }
}

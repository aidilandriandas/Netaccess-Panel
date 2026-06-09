<?php

namespace App\Services\Hosting;

use App\Models\HostingAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class DirectAdminProvisioner implements HostingProvisionerInterface
{
    protected string $host;
    protected string $user;
    protected string $password;
    protected int $port;

    public function __construct()
    {
        $this->host     = Setting::get('directadmin_host', '');
        $this->user     = Setting::get('directadmin_user', 'admin');
        $this->password = Setting::get('directadmin_password', '');
        $this->port     = (int) Setting::get('directadmin_port', '2222');
    }

    public function createAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            Log::info("DirectAdmin belum dikonfigurasi. Placeholder: create {$account->username}");
            return ['success' => true, 'message' => 'DirectAdmin not configured, placeholder success.'];
        }

        $params = [
            'action'   => 'create',
            'add'      => 'Submit',
            'username' => $account->username,
            'domain'   => $account->domain,
            'passwd'   => $account->password,
            'passwd2'  => $account->password,
            'package'  => $account->hostingPackage->cpanel_package ?? 'default',
            'email'    => $account->customer->email ?? '',
            'notify'   => 'no',
        ];

        $result = $this->apiCall('/CMD_API_ACCOUNT_USER', $params, 'POST');

        if ($result && isset($result['error']) && $result['error'] == 0) {
            Log::info("DirectAdmin: Account created for {$account->username}");
            return ['success' => true, 'message' => 'Account created.'];
        }

        $error = $result['text'] ?? $result['details'] ?? 'Unknown error';
        Log::error("DirectAdmin: Failed to create {$account->username}: {$error}");
        return ['success' => false, 'message' => $error];
    }

    public function suspendAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            return ['success' => true, 'message' => 'DirectAdmin not configured, placeholder success.'];
        }

        $result = $this->apiCall('/CMD_API_SELECT_USERS', [
            'location'  => 'CMD_SELECT_USERS',
            'suspend'   => 'Suspend',
            'select0'   => $account->username,
        ], 'POST');

        if ($result && (!isset($result['error']) || $result['error'] == 0)) {
            return ['success' => true, 'message' => 'Account suspended.'];
        }

        return ['success' => false, 'message' => $result['text'] ?? 'Unknown error'];
    }

    public function unsuspendAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            return ['success' => true, 'message' => 'DirectAdmin not configured, placeholder success.'];
        }

        $result = $this->apiCall('/CMD_API_SELECT_USERS', [
            'location'  => 'CMD_SELECT_USERS',
            'unsuspend' => 'Unsuspend',
            'select0'   => $account->username,
        ], 'POST');

        if ($result && (!isset($result['error']) || $result['error'] == 0)) {
            return ['success' => true, 'message' => 'Account unsuspended.'];
        }

        return ['success' => false, 'message' => $result['text'] ?? 'Unknown error'];
    }

    public function terminateAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            return ['success' => true, 'message' => 'DirectAdmin not configured, placeholder success.'];
        }

        $result = $this->apiCall('/CMD_API_SELECT_USERS', [
            'confirmed' => 'Confirm',
            'delete'    => 'yes',
            'select0'   => $account->username,
        ], 'POST');

        if ($result && (!isset($result['error']) || $result['error'] == 0)) {
            return ['success' => true, 'message' => 'Account terminated.'];
        }

        return ['success' => false, 'message' => $result['text'] ?? 'Unknown error'];
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'DirectAdmin belum dikonfigurasi.'];
        }

        $result = $this->apiCall('/CMD_API_SHOW_RESELLER_IPS');

        if ($result !== null) {
            return ['success' => true, 'message' => 'Terhubung ke DirectAdmin.'];
        }

        return ['success' => false, 'message' => 'Gagal terhubung ke DirectAdmin.'];
    }

    protected function isConfigured(): bool
    {
        return !empty($this->host) && !empty($this->password);
    }

    protected function apiCall(string $path, array $params = [], string $method = 'GET'): ?array
    {
        $url = "https://{$this->host}:{$this->port}{$path}";

        try {
            $ch = curl_init();
            $opts = [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_USERPWD        => "{$this->user}:{$this->password}",
            ];

            if ($method === 'POST') {
                $opts[CURLOPT_POST] = true;
                $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
            } elseif (!empty($params)) {
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            }

            curl_setopt_array($ch, $opts);
            $response = curl_exec($ch);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error("DirectAdmin cURL error: {$error}");
                return null;
            }

            $decoded = json_decode($response, true);
            if ($decoded !== null) {
                return $decoded;
            }

            parse_str($response, $parsed);
            return !empty($parsed) ? $parsed : null;
        } catch (\Throwable $e) {
            Log::error("DirectAdmin API exception: {$e->getMessage()}");
            return null;
        }
    }
}

<?php

namespace App\Services\Hosting;

use App\Models\HostingAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class WhmProvisioner implements HostingProvisionerInterface
{
    protected string $host;
    protected string $token;
    protected int $port;

    public function __construct()
    {
        $this->host  = Setting::get('whm_host', '');
        $this->token = Setting::get('whm_api_token', '');
        $this->port  = (int) Setting::get('whm_port', '2087');
    }

    public function createAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            Log::info("WHM belum dikonfigurasi. Placeholder: create account {$account->username}");
            return ['success' => true, 'message' => 'WHM not configured, placeholder success.'];
        }

        $params = [
            'username' => $account->username,
            'domain'   => $account->domain,
            'password' => $account->password,
            'plan'     => $account->hostingPackage->cpanel_package ?? 'default',
        ];

        $result = $this->apiCall('createacct', $params);

 	$isSuccess =
   	 (int) ($result['metadata']['result'] ?? 0) === 1 ||
   	 (int) ($result['result'][0]['status'] ?? 0) === 1;

	if ($isSuccess) {
   	 Log::info("WHM: Account created for {$account->username}");

    	return [
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => $result,
            'server_ip' => $result['result'][0]['options']['ip'] ?? null,
    ];
}

$error =
    $result['metadata']['reason']
    ?? $result['result'][0]['statusmsg']
    ?? $result['result'][0]['rawout']
    ?? 'Unknown error';

Log::error("WHM: Failed to create account {$account->username}: {$error}");
Log::error('WHM create account failed detail', [
    'username' => $account->username ?? null,
    'domain' => $account->domain ?? null,
    'package' => $account->hostingPackage->cpanel_package ?? null,
    'error' => $error,
    'result' => $result,
]);

return ['success' => false, 'message' => $error];
    }

    public function suspendAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            Log::info("WHM belum dikonfigurasi. Placeholder: suspend {$account->username}");
            return ['success' => true, 'message' => 'WHM not configured, placeholder success.'];
        }

        $result = $this->apiCall('suspendacct', [
            'user'   => $account->username,
            'reason' => 'Suspended via NetAccess Panel',
        ]);

        if ($result && isset($result['metadata']['result']) && $result['metadata']['result'] == 1) {
            Log::info("WHM: Account suspended for {$account->username}");
            return ['success' => true, 'message' => 'Account suspended.'];
        }

        $error = $result['metadata']['reason'] ?? 'Unknown error';
        Log::error("WHM: Failed to suspend {$account->username}: {$error}");
        return ['success' => false, 'message' => $error];
    }

    public function unsuspendAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            Log::info("WHM belum dikonfigurasi. Placeholder: unsuspend {$account->username}");
            return ['success' => true, 'message' => 'WHM not configured, placeholder success.'];
        }

        $result = $this->apiCall('unsuspendacct', [
            'user' => $account->username,
        ]);

        if ($result && isset($result['metadata']['result']) && $result['metadata']['result'] == 1) {
            Log::info("WHM: Account unsuspended for {$account->username}");
            return ['success' => true, 'message' => 'Account unsuspended.'];
        }

        $error = $result['metadata']['reason'] ?? 'Unknown error';
        Log::error("WHM: Failed to unsuspend {$account->username}: {$error}");
        return ['success' => false, 'message' => $error];
    }

    public function terminateAccount(HostingAccount $account): array
    {
        if (!$this->isConfigured()) {
            Log::info("WHM belum dikonfigurasi. Placeholder: terminate {$account->username}");
            return ['success' => true, 'message' => 'WHM not configured, placeholder success.'];
        }
	$result = $this->apiCall('removeacct', [
    'username' => $account->username,
]);

$isSuccess =
    (int) ($result['metadata']['result'] ?? 0) === 1 ||
    (int) ($result['result'][0]['status'] ?? 0) === 1;

if ($isSuccess) {
    Log::info("WHM: Account terminated for {$account->username}");

    return [
        'success' => true,
        'message' => 'Account terminated.',
        'data' => $result,
    ];
}

$error =
    $result['metadata']['reason']
    ?? $result['result'][0]['statusmsg']
    ?? $result['result'][0]['rawout']
    ?? 'Unknown error';

Log::error("WHM: Failed to terminate {$account->username}: {$error}");
Log::error('WHM terminate account failed detail', [
    'username' => $account->username ?? null,
    'domain' => $account->domain ?? null,
    'error' => $error,
    'result' => $result,
]);

return ['success' => false, 'message' => $error];
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'WHM belum dikonfigurasi.'];
        }

        $result = $this->apiCall('version');

        if ($result && isset($result['version'])) {
            return ['success' => true, 'message' => 'Terhubung ke WHM. Version: ' . $result['version']];
        }

        return ['success' => false, 'message' => 'Gagal terhubung ke WHM.'];
    }

    protected function isConfigured(): bool
    {
        return !empty($this->host) && !empty($this->token);
    }

    protected function apiCall(string $function, array $params = []): ?array
    {
        $url = "https://{$this->host}:{$this->port}/json-api/{$function}";

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: whm root:' . $this->token,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error("WHM cURL error: {$error}");
                return null;
            }

            if ($httpCode !== 200) {
                Log::error("WHM HTTP error: {$httpCode}");
                return null;
            }

            return json_decode($response, true);
        } catch (\Throwable $e) {
            Log::error("WHM API exception: {$e->getMessage()}");
            return null;
        }
    }
}

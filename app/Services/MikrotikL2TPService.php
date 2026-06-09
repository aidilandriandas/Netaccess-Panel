<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\VpnUser;
use Illuminate\Support\Facades\Log;

class MikrotikL2TPService
{
    protected string $host;
    protected string $user;
    protected string $password;
    protected int $port;

    public function __construct()
    {
     	$this->host     = (string) (Setting::get('mikrotik_host') ?? '');
        $this->user     = (string) (Setting::get('mikrotik_user') ?? 'admin');
   	$this->password = (string) (Setting::get('mikrotik_password') ?? '');
    	$this->port     = (int) (Setting::get('mikrotik_api_port') ?? 8728);
    }

    // -------------------------------------------------------------------------
    // Credential helpers
    // -------------------------------------------------------------------------

    public function generateCredentials(string $customerName): array
    {
        $base     = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $customerName));
        $base     = substr($base, 0, 15);
        $username = $base . '_l2tp_' . rand(100, 999);
        $counter  = 0;

        while (VpnUser::where('username', $username)->exists()) {
            $counter++;
            $username = $base . '_l2tp_' . ($counter < 900 ? rand(100, 999) : $counter + 100);
            if ($counter > 50) {
                $username = $base . '_l2tp_' . uniqid();
                break;
            }
        }

        // Generate a random secure password
        $password = bin2hex(random_bytes(8)); // 16-char hex

        return [
            'username' => $username,
            'password' => $password,
        ];
    }

    public function getNextAvailableIp(): string
    {
        $pool = Setting::get('l2tp_ip_pool', '192.168.100.0/24');
        [$baseNet] = explode('/', $pool);
        $octets = explode('.', $baseNet);

        $lastUser = VpnUser::whereNotNull('assigned_ip')
            ->orderByRaw(
                "CAST(SUBSTRING_INDEX(assigned_ip, '.', -1) AS UNSIGNED) DESC"
            )
            ->first();

        if ($lastUser && $lastUser->assigned_ip) {
            $lastOctets = explode('.', $lastUser->assigned_ip);
            $nextHost   = ((int) $lastOctets[3]) + 1;
        } else {
            $nextHost = 2; // .1 reserved for Mikrotik gateway
        }

        if ($nextHost > 254) {
            throw new \RuntimeException('IP pool exhausted. Tambah range pool di Settings.');
        }

        return $octets[0] . '.' . $octets[1] . '.' . $octets[2] . '.' . $nextHost;
    }

    // -------------------------------------------------------------------------
    // Mikrotik API via RouterOS REST API (v7+) or plain socket fallback
    // -------------------------------------------------------------------------

    /**
     * Tambahkan user L2TP ke Mikrotik PPP Secrets.
     */
    public function addUser(VpnUser $vpnUser): bool
    {
        if (!$this->isMikrotikConfigured()) {
            Log::info("Mikrotik belum dikonfigurasi. Placeholder: tambah user {$vpnUser->username}");
            return true;
        }

        $result = $this->apiCall('/ppp/secret/add', [
            'name'             => $vpnUser->username,
            'password'         => $vpnUser->l2tp_password,
            'service'          => 'l2tp',
            'local-address'    => Setting::get('l2tp_local_address', '192.168.100.1'),
            'remote-address'   => $vpnUser->assigned_ip,
            'profile'          => Setting::get('l2tp_profile', 'default-encryption'),
            'comment'          => 'netaccess-panel|' . $vpnUser->id,
        ]);

        if ($result === false) {
            Log::error("Gagal menambah user L2TP: {$vpnUser->username}");
            return false;
        }

        Log::info("User L2TP ditambahkan: {$vpnUser->username}");
        return true;
    }

    /**
     * Hapus user dari PPP Secrets.
     */
    public function removeUser(VpnUser $vpnUser): bool
    {
        if (!$this->isMikrotikConfigured()) {
            Log::info("Mikrotik belum dikonfigurasi. Placeholder: hapus user {$vpnUser->username}");
            return true;
        }

        // Cari PPP Secret berdasarkan username
        $secrets = $this->apiCall('/ppp/secret', ['name' => $vpnUser->username], 'GET');

        if (empty($secrets) || !isset($secrets[0]['.id'])) {
            Log::warning("User L2TP tidak ditemukan di MikroTik saat terminate: {$vpnUser->username}");

            // Kalau di MikroTik memang sudah tidak ada, anggap sukses supaya panel bisa bersih
            return true;
        }

        $id = $secrets[0]['.id'];

        // RouterOS REST lebih kompatibel pakai command remove + numbers
        $result = $this->apiCall('/ppp/secret/remove', [
            'numbers' => $id,
        ], 'POST');

        if ($result === false) {
            Log::error("Gagal hapus user L2TP di MikroTik: {$vpnUser->username}", [
                'mikrotik_id' => $id,
            ]);

            return false;
        }

        Log::info("User L2TP dihapus dari MikroTik: {$vpnUser->username}", [
            'mikrotik_id' => $id,
        ]);

        return true;
    }





    /**
     * Suspend = disable secret di Mikrotik.
     */
    public function suspendUser(VpnUser $vpnUser): bool
    {
        if (!$this->isMikrotikConfigured()) {
            Log::info("Placeholder: suspend user {$vpnUser->username}");
            return true;
        }

        $secrets = $this->apiCall('/ppp/secret', ['name' => $vpnUser->username], 'GET');

        if (empty($secrets) || !isset($secrets[0]['.id'])) {
            Log::warning("User L2TP tidak ditemukan di MikroTik saat suspend: {$vpnUser->username}");
            return false;
        }

        $id = $secrets[0]['.id'];

        $result = $this->apiCall('/ppp/secret/' . $id, [
            'disabled' => 'true',
        ], 'PATCH');

        if ($result === false) {
            Log::error("Gagal suspend user L2TP di MikroTik: {$vpnUser->username}");
            return false;
        }

        $this->disconnectActiveSession($vpnUser->username);

        Log::info("User L2TP di-suspend di MikroTik: {$vpnUser->username}");

        return true;
    }



    /**
     * Unsuspend = enable kembali.
     */
    public function unsuspendUser(VpnUser $vpnUser): bool
    {
        if (!$this->isMikrotikConfigured()) {
            Log::info("Placeholder: unsuspend user {$vpnUser->username}");
            return true;
        }

        $secrets = $this->apiCall('/ppp/secret', ['name' => $vpnUser->username], 'GET');

        if (empty($secrets) || !isset($secrets[0]['.id'])) {
            Log::warning("User L2TP tidak ditemukan di MikroTik saat unsuspend: {$vpnUser->username}");
            return false;
        }

        $id = $secrets[0]['.id'];

        $result = $this->apiCall('/ppp/secret/' . $id, [
            'disabled' => 'false',
        ], 'PATCH');

        if ($result === false) {
            Log::error("Gagal unsuspend user L2TP di MikroTik: {$vpnUser->username}");
            return false;
        }

        Log::info("User L2TP di-unsuspend di MikroTik: {$vpnUser->username}");

        return true;
    }



    /**
     * Disconnect sesi PPP aktif (paksa re-login).
     */
    public function disconnectActiveSession(string $username): bool
    {
        if (!$this->isMikrotikConfigured()) {
            return true;
        }

        $active = $this->apiCall('/ppp/active', ['name' => $username], 'GET');

        if (empty($active) || !isset($active[0]['.id'])) {
            return true;
        }

        $id = $active[0]['.id'];

        $result = $this->apiCall('/ppp/active/' . $id, [], 'DELETE');

        if ($result === false) {
            Log::warning("Gagal disconnect session aktif PPP: {$username}");
            return false;
        }

        Log::info("Session PPP aktif diputus: {$username}");

        return true;
    }



    /**
     * Ambil info koneksi aktif user.
     */
    public function getActiveSession(string $username): ?array
    {
        if (!$this->isMikrotikConfigured()) {
            return null;
        }

        $active = $this->apiCall('/ppp/active/print', ['?name' => $username], 'GET');
        return !empty($active) ? $active[0] : null;
    }

    /**
     * Test koneksi ke Mikrotik API.
     */
    public function testConnection(): array
    {
        if (!$this->isMikrotikConfigured()) {
            return ['success' => false, 'message' => 'Mikrotik belum dikonfigurasi. Isi host, user, dan password di Settings.'];
        }

        $result = $this->apiCall('/system/identity/print', [], 'GET');

        if ($result !== false && isset($result[0]['name'])) {
            return ['success' => true, 'message' => 'Terhubung ke: ' . $result[0]['name']];
        }

        return ['success' => false, 'message' => 'Gagal terhubung ke Mikrotik. Cek host/credentials/API port.'];
    }

    // -------------------------------------------------------------------------
    // Mikrotik REST API (RouterOS v7+)
    // -------------------------------------------------------------------------

    protected function apiCall(string $path, array $body = [], string $method = 'POST'): mixed
    {
        $url  = 'http://' . $this->host . ':' . $this->port . '/rest' . $path;
        $auth = base64_encode($this->user . ':' . $this->password);

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $auth,
            ],
        ];

        if ($method === 'POST' || $method === 'PATCH') {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            $options[CURLOPT_POSTFIELDS]    = json_encode($body);
        } elseif ($method === 'GET') {
            $options[CURLOPT_CUSTOMREQUEST] = 'GET';
            if (!empty($body)) {
                $url .= '?' . http_build_query($body);
                $options[CURLOPT_URL] = $url;
            }
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error("Mikrotik API cURL error: {$error}");
            return false;
        }

        if ($httpCode >= 400) {
            Log::error("Mikrotik API HTTP {$httpCode}: {$response}");
            return false;
        }

        return json_decode($response, true) ?? [];
    }

    protected function isMikrotikConfigured(): bool
    {
        return !empty($this->host) && !empty($this->user) && !empty($this->password);
    }

    // -------------------------------------------------------------------------
    // Panduan koneksi untuk client
    // -------------------------------------------------------------------------

    public function createClientInstructions(VpnUser $vpnUser): string
    {
        $server = Setting::get('mikrotik_public_ip')
            ?? Setting::get('mikrotik_host')
            ?? '0.0.0.0';

        $secret = Setting::get('mikrotik_ipsec_secret')
            ?? Setting::get('ipsec_preshared_key')
            ?? Setting::get('l2tp_ipsec_secret')
            ?? '-';

        $password = $vpnUser->l2tp_password
            ?? $vpnUser->password
            ?? '-';

        $expired = $vpnUser->expired_at
            ? $vpnUser->expired_at->format('d M Y')
            : '-';

        return "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "        DETAIL AKUN VPN\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "Tipe VPN        : L2TP/IPSec\n"
            . "Server VPN      : {$server}\n"
            . "Username        : {$vpnUser->username}\n"
            . "Password        : {$password}\n"
            . "IPSec Key       : {$secret}\n"
            . "Status          : {$vpnUser->status}\n"
            . "Expired         : {$expired}\n\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            . "PANDUAN SETTING\n"
            . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
            . "Android:\n"
            . "1. Buka Settings > VPN\n"
            . "2. Tambah VPN baru\n"
            . "3. Pilih tipe L2TP/IPSec PSK\n"
            . "4. Isi Server VPN, Username, Password, dan IPSec Key\n\n"
            . "iPhone/iPad:\n"
            . "1. Buka Settings > General > VPN & Device Management\n"
            . "2. Add VPN Configuration\n"
            . "3. Type: L2TP\n"
            . "4. Isi Server, Account, Password, dan Secret\n\n"
            . "Windows:\n"
            . "1. Buka Settings > Network & Internet > VPN\n"
            . "2. Add VPN\n"
            . "3. VPN Provider: Windows built-in\n"
            . "4. VPN Type: L2TP/IPSec with pre-shared key\n\n"
            . "Catatan:\n"
            . "- Gunakan Server VPN untuk koneksi.\n"
            . "- IP private VPN tidak perlu diisi manual.\n"
            . "- Jika gagal konek, pastikan username, password, dan IPSec Key benar.\n";
    }



    public function createUser(\App\Models\VpnUser $vpnUser): array
    {
        try {
            if (!method_exists($this, 'addUser')) {
                return [
                    'success' => false,
                    'message' => 'Method addUser tidak ditemukan di MikrotikL2TPService.',
                ];
            }

            $success = $this->addUser($vpnUser);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'User L2TP berhasil dibuat di MikroTik.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal menambahkan user L2TP ke MikroTik.',
            ];
        } catch (\Throwable $e) {
            \Log::error('Mikrotik createUser wrapper error', [
                'vpn_user_id' => $vpnUser->id ?? null,
                'username' => $vpnUser->username ?? null,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

}

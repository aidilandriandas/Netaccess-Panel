<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class VpsServer extends Model
{
    protected $fillable = [
        'name', 'provider_type', 'host', 'api_url',
        'api_username', 'api_token', 'api_secret',
        'ssh_port', 'location', 'status', 'notes',
    ];

    public function setApiTokenAttribute(?string $value): void
    {
        $this->attributes['api_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiTokenAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function setApiSecretAttribute(?string $value): void
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiSecretAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function getMaskedApiToken(): string
    {
        $token = $this->api_token;
        if (!$token) {
            return '-';
        }
        return substr($token, 0, 6) . str_repeat('*', max(0, strlen($token) - 10)) . substr($token, -4);
    }

    public function getMaskedApiSecret(): string
    {
        $secret = $this->api_secret;
        if (!$secret) {
            return '-';
        }
        return substr($secret, 0, 4) . str_repeat('*', max(0, strlen($secret) - 8)) . substr($secret, -4);
    }

    public function vpsServices(): HasMany
    {
        return $this->hasMany(VpsService::class);
    }
}

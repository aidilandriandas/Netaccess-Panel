<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class VpsService extends Model
{
    protected $fillable = [
        'customer_id', 'vps_package_id', 'vps_server_id',
        'hostname', 'main_ip', 'username', 'password',
        'ssh_port', 'os_name', 'cpu_cores', 'ram_mb',
        'storage_gb', 'bandwidth_gb', 'status',
        'started_at', 'expired_at', 'provisioned_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expired_at' => 'datetime',
            'provisioned_at' => 'datetime',
        ];
    }

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getPasswordAttribute(?string $value): ?string
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vpsPackage(): BelongsTo
    {
        return $this->belongsTo(VpsPackage::class);
    }

    public function vpsServer(): BelongsTo
    {
        return $this->belongsTo(VpsServer::class);
    }

    public function vpsOrders(): HasMany
    {
        return $this->hasMany(VpsOrder::class);
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function daysUntilExpiry(): ?int
    {
        if (!$this->expired_at) {
            return null;
        }
        return (int) now()->diffInDays($this->expired_at, false);
    }
}

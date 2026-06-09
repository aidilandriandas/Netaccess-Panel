<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingAccount extends Model
{
    protected $fillable = [
        'customer_id', 'hosting_package_id', 'domain', 'username',
        'password', 'server_type', 'status', 'server_ip',
        'started_at', 'expired_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function hostingPackage(): BelongsTo
    {
        return $this->belongsTo(HostingPackage::class);
    }

    public function hostingOrders(): HasMany
    {
        return $this->hasMany(HostingOrder::class);
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

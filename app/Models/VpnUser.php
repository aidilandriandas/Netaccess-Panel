<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VpnUser extends Model
{
    protected $fillable = [
        'customer_id', 'package_id', 'username', 'l2tp_password',
        'assigned_ip', 'status', 'started_at', 'expired_at', 'last_seen_at',
        'upload_usage', 'download_usage',
    ];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'expired_at'  => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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

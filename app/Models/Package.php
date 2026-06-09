<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name',
        'service_type',
        'description',
        'price',
        'setup_fee',
        'duration_days',
        'max_users',
        'bandwidth_limit',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'duration_days' => 'integer',
        'max_users' => 'integer',
        'is_active' => 'boolean',
    ];

    public function vpnUsers(): HasMany
    {
        return $this->hasMany(VpnUser::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

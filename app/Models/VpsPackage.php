<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VpsPackage extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'billing_cycle',
        'cpu_cores', 'ram_mb', 'storage_gb', 'bandwidth_gb',
        'ipv4_count', 'os_options', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'os_options' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function vpsServices(): HasMany
    {
        return $this->hasMany(VpsService::class);
    }

    public function vpsOrders(): HasMany
    {
        return $this->hasMany(VpsOrder::class);
    }

    public function isInUse(): bool
    {
        return $this->vpsServices()->exists() || $this->vpsOrders()->exists();
    }
}

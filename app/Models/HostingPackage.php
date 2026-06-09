<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingPackage extends Model
{
    protected $fillable = [
        'name', 'price', 'setup_fee', 'duration_days',
        'disk_space_mb', 'bandwidth_mb', 'max_email_accounts',
        'max_databases', 'max_addon_domains', 'max_parked_domains',
        'max_subdomains', 'cpanel_package', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'setup_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function hostingAccounts(): HasMany
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function hostingOrders(): HasMany
    {
        return $this->hasMany(HostingOrder::class);
    }
}

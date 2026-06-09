<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address', 'company_name',
        'customer_type', 'status', 'notes',
    ];

    public function vpnUsers(): HasMany
    {
        return $this->hasMany(VpnUser::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activeVpnUsers(): HasMany
    {
        return $this->vpnUsers()->where('status', 'active');
    }

    public function hostingAccounts(): HasMany
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function hostingOrders(): HasMany
    {
        return $this->hasMany(HostingOrder::class);
    }

    public function vpsServices(): HasMany
    {
        return $this->hasMany(VpsService::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
    public function vpsOrders()
    {
        return $this->hasMany(\App\Models\VpsOrder::class);
    }
}

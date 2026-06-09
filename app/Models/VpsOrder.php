<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VpsOrder extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'vps_package_id',
        'vps_service_id', 'invoice_id', 'billing_cycle',
        'hostname', 'os_name', 'price', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'VPS-' . now()->format('Ym') . '-';
        $lastOrder = static::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vpsPackage(): BelongsTo
    {
        return $this->belongsTo(VpsPackage::class);
    }

    public function vpsService(): BelongsTo
    {
        return $this->belongsTo(VpsService::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

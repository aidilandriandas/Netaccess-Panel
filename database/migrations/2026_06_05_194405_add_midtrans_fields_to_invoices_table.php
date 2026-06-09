<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('status');
            }

            if (!Schema::hasColumn('invoices', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->after('payment_gateway');
            }

            if (!Schema::hasColumn('invoices', 'gateway_status')) {
                $table->string('gateway_status')->nullable()->after('gateway_order_id');
            }

            if (!Schema::hasColumn('invoices', 'payment_token')) {
                $table->text('payment_token')->nullable()->after('gateway_status');
            }

            if (!Schema::hasColumn('invoices', 'payment_redirect_url')) {
                $table->text('payment_redirect_url')->nullable()->after('payment_token');
            }

            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            foreach ([
                'payment_gateway',
                'gateway_order_id',
                'gateway_status',
                'payment_token',
                'payment_redirect_url',
                'paid_at',
            ] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

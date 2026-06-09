<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('hosting_package_id')->constrained()->onDelete('restrict');
            $table->foreignId('hosting_account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->string('domain');
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'provisioning', 'active', 'failed', 'cancelled'])->default('pending');
            $table->text('provision_log')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_orders');
    }
};

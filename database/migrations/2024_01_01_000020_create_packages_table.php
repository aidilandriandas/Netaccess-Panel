<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('service_type', ['vpn', 'monitoring', 'hosting', 'backup', 'vps'])->default('vpn');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('setup_fee', 12, 2)->default(0);
            $table->integer('duration_days')->default(30);
            $table->integer('max_users')->default(1);
            $table->string('bandwidth_limit')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

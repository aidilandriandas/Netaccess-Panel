<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vpn_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('restrict');
            $table->string('username')->unique();
            $table->string('l2tp_password');          // Password PPP Mikrotik
            $table->string('assigned_ip')->nullable(); // Remote IP dari pool
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->bigInteger('upload_usage')->default(0);
            $table->bigInteger('download_usage')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vpn_users');
    }
};

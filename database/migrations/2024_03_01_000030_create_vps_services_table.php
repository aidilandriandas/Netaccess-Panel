<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vps_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('vps_package_id')->constrained();
            $table->foreignId('vps_server_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hostname');
            $table->string('main_ip')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->integer('ssh_port')->default(22);
            $table->string('os_name')->nullable();
            $table->integer('cpu_cores');
            $table->integer('ram_mb');
            $table->integer('storage_gb');
            $table->integer('bandwidth_gb')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'expired', 'terminated', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vps_services');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vps_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('provider_type', ['manual', 'proxmox', 'virtualizor', 'solusvm', 'cloud_api'])->default('manual');
            $table->string('host');
            $table->string('api_url')->nullable();
            $table->string('api_username')->nullable();
            $table->text('api_token')->nullable();
            $table->text('api_secret')->nullable();
            $table->integer('ssh_port')->default(22);
            $table->string('location');
            $table->enum('status', ['active', 'maintenance', 'offline'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vps_servers');
    }
};

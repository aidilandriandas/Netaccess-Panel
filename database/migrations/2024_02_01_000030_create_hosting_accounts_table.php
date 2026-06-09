<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('hosting_package_id')->constrained()->onDelete('restrict');
            $table->string('domain');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('server_type', ['whm', 'directadmin'])->default('whm');
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending');
            $table->string('server_ip')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_accounts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('setup_fee', 12, 2)->default(0);
            $table->integer('duration_days')->default(30);
            $table->integer('disk_space_mb')->default(1000);
            $table->integer('bandwidth_mb')->nullable();
            $table->integer('max_email_accounts')->default(5);
            $table->integer('max_databases')->default(3);
            $table->integer('max_addon_domains')->default(0);
            $table->integer('max_parked_domains')->default(0);
            $table->integer('max_subdomains')->default(5);
            $table->string('cpanel_package')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_packages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'service_type')) {
                $table->string('service_type')->nullable()->after('priority');
            }

            if (!Schema::hasColumn('support_tickets', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->after('service_type');
            }

            if (!Schema::hasColumn('support_tickets', 'service_label')) {
                $table->string('service_label')->nullable()->after('service_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'service_label')) {
                $table->dropColumn('service_label');
            }

            if (Schema::hasColumn('support_tickets', 'service_id')) {
                $table->dropColumn('service_id');
            }

            if (Schema::hasColumn('support_tickets', 'service_type')) {
                $table->dropColumn('service_type');
            }
        });
    }
};

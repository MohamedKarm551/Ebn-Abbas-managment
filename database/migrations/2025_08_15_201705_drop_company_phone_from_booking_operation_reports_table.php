<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('booking_operation_reports', function (Blueprint $table) {
            if (Schema::hasColumn('booking_operation_reports', 'company_phone')) {
                $table->dropColumn('company_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_operation_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_operation_reports', 'company_phone')) {
                $table->string('company_phone')->nullable();
            }
        });
    }
};

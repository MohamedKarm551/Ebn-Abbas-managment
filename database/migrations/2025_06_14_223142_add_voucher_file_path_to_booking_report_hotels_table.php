<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoucherFilePathToBookingReportHotelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_report_hotels', function (Blueprint $table) {
            $table->string('voucher_file_path')->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_report_hotels', function (Blueprint $table) {
            $table->dropColumn('voucher_file_path');
        });
    }
};

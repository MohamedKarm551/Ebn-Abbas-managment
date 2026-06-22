<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_details', function (Blueprint $table) {
            $table->foreignId('booking_id')
                  ->nullable()
                  ->constrained('bookings')
                  ->nullOnDelete()
                  ->after('credit_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('voucher_details', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
        });
    }
};
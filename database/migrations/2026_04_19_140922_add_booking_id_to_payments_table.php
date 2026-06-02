<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        if (!Schema::hasColumn('payments', 'booking_id')) {
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
        }
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // حذف foreign key أولاً (مهم جداً!)
            if (Schema::hasColumn('payments', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }
        });
    }
};

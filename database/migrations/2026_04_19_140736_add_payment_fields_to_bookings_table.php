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
    Schema::table('bookings', function (Blueprint $table) {
        if (!Schema::hasColumn('bookings', 'amount_paid_by_company')) {
            $table->decimal('amount_paid_by_company', 12, 2)->default(0)->after('amount_due_from_company');
        }
        if (!Schema::hasColumn('bookings', 'payment_status')) {
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('amount_paid_by_company');
        }
    });
}

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // حذف الأعمدة بترتيب عكسي (العكس تماماً)
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('bookings', 'amount_paid_by_company')) {
                $table->dropColumn('amount_paid_by_company');
            }
        });
    }
};

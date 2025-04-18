<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            // حذف الأعمدة إذا كانت موجودة
            if (Schema::hasColumn('bookings', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('bookings', 'employee_name')) {
                $table->dropColumn('employee_name');
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }

            // إعادة الأعمدة المحذوفة
            if (!Schema::hasColumn('bookings', 'amount_due_to_hotel')) {
                $table->decimal('amount_due_to_hotel', 10, 2)->default(0)->after('sale_price');
            }
            if (!Schema::hasColumn('bookings', 'amount_due_from_company')) {
                $table->decimal('amount_due_from_company', 10, 2)->default(0)->after('amount_due_to_hotel');
            }
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'amount_due_to_hotel')) {
                $table->dropColumn('amount_due_to_hotel');
            }
            if (Schema::hasColumn('bookings', 'amount_due_from_company')) {
                $table->dropColumn('amount_due_from_company');
            }
            if (Schema::hasColumn('bookings', 'company_name')) {
                $table->dropColumn('company_name');
            }
            if (Schema::hasColumn('bookings', 'employee_name')) {
                $table->dropColumn('employee_name');
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};

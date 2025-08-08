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
            // إضافة حقل جديد لأرباح الموظف
            $table->decimal('employee_profit', 12, 2)->default(0)->after('grand_total_profit');

            // إضافة حقل عملة أرباح الموظف (ثابت EGP)
            $table->string('employee_profit_currency')->default('EGP')->after('employee_profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_operation_reports', function (Blueprint $table) {
            $table->dropColumn('employee_profit');
            $table->dropColumn('employee_profit_currency');
        });
    }
};

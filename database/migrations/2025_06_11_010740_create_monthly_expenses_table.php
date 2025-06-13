<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyExpensesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('month_year');
            $table->decimal('salaries', 10, 2)->default(0);
            $table->decimal('advertising', 10, 2)->default(0);
            $table->decimal('rent', 10, 2)->default(0);
            $table->decimal('staff_commissions', 10, 2)->default(0);
            $table->json('other_expenses')->nullable();

            // إجمالي الربح بكل عملة
            $table->decimal('total_monthly_profit_SAR', 12, 2)->default(0);
            $table->decimal('total_monthly_profit_KWD', 12, 2)->default(0);

            $table->json('expenses_currencies')->nullable(); // لتخزين عملة كل مصروف
            $table->string('unified_currency')->nullable(); // العملة الموحدة المستخدمة
            $table->decimal('exchange_rate', 12, 5)->nullable(); // سعر الصرف المستخدم
            // صافي الربح بكل عملة
            $table->decimal('net_profit_SAR', 12, 2)->default(0);
            $table->decimal('net_profit_KWD', 12, 2)->default(0);

            // صافي الربح العام (للتوافقية)
            $table->decimal('net_profit', 12, 2)->default(0);

            // توزيع الأرباح بكل عملة
            $table->decimal('ismail_share_SAR', 12, 2)->default(0);
            $table->decimal('ismail_share_KWD', 12, 2)->default(0);
            $table->decimal('ismail_share', 12, 2)->default(0);

            $table->decimal('mohamed_share_SAR', 12, 2)->default(0);
            $table->decimal('mohamed_share_KWD', 12, 2)->default(0);
            $table->decimal('mohamed_share', 12, 2)->default(0);

            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_expenses');
    }
};

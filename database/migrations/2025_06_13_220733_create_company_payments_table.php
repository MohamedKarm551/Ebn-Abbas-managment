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
        Schema::create('company_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // المبلغ المدفوع
            $table->string('currency', 3)->default('KWD'); // العملة
            $table->date('payment_date'); // تاريخ الدفع
            $table->text('notes')->nullable(); // ملاحظات
            $table->string('receipt_image_url')->nullable(); // رابط صورة الإيصال
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete(); // الموظف الذي سجل الدفعة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_payments');
    }
};

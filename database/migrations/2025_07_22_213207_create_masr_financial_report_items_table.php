<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إنشاء جدول بنود تقارير شركة مصر
     */
    public function up(): void
    {
        Schema::create('masr_financial_report_items', function (Blueprint $table) {
            $table->id(); // معرف البند
            $table->unsignedBigInteger('report_id'); // معرف التقرير الرئيسي
            $table->string('title'); // عنوان البند (مثلاً: تأشيرة، تذكرة نقل...)
            $table->decimal('cost_amount', 12, 2); // قيمة التكلفة
            $table->string('cost_currency')->default('EGP'); // عملة التكلفة
            $table->decimal('sale_amount', 12, 2)->nullable(); // قيمة البيع
            $table->string('sale_currency')->default('EGP'); // عملة البيع
            $table->timestamps(); // وقت الإنشاء والتعديل

            // ربط البند بالتقرير الرئيسي وحذف البنود عند حذف التقرير
            $table->foreign('report_id')->references('id')->on('masr_financial_reports')->onDelete('cascade');
        });
    }

    /**
     * حذف جدول البنود إذا تم التراجع عن الترحيل
     */
    public function down(): void
    {
        Schema::dropIfExists('masr_financial_report_items');
    }
};

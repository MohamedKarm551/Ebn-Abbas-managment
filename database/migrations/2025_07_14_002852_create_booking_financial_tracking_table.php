<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration لإنشاء جدول متابعة المعاملات المالية للحجوزات
 * 
 * هذا الجدول يحتوي على:
 * - معلومات الحجز المرتبط
 * - حالة السداد لجهة الحجز (agent)
 * - حالة السداد للشركة (company)
 * - المبالغ والملاحظات
 * - معلومات التتبع والتحديث
 */
return new class extends Migration
{
    /**
     * تشغيل الـ Migration
     */
    public function up()
    {
        Schema::create('booking_financial_tracking', function (Blueprint $table) {
            // المعرف الأساسي
            $table->id();
            
            // ربط الحجز (Foreign Key)
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            
            // ===== بيانات جهة الحجز (Agent) =====
            // حالة السداد: لم يتم - جزئي - كامل
            $table->enum('agent_payment_status', ['not_paid', 'partially_paid', 'fully_paid'])
                  ->default('not_paid')
                  ->comment('حالة السداد من جهة الحجز');
            
            // المبلغ المدفوع من جهة الحجز
            $table->decimal('agent_payment_amount', 10, 2)
                  ->default(0)
                  ->comment('المبلغ المدفوع من جهة الحجز');
            
            // ملاحظات الدفع لجهة الحجز
            $table->text('agent_payment_notes')
                  ->nullable()
                  ->comment('ملاحظات الدفع لجهة الحجز');
            
            // ===== بيانات الشركة (Company) =====
            // حالة السداد للشركة
            $table->enum('company_payment_status', ['not_paid', 'partially_paid', 'fully_paid'])
                  ->default('not_paid')
                  ->comment('حالة السداد للشركة');
            
            // المبلغ المدفوع للشركة
            $table->decimal('company_payment_amount', 10, 2)
                  ->default(0)
                  ->comment('المبلغ المدفوع للشركة');
            
            // ملاحظات الدفع للشركة
            $table->text('company_payment_notes')
                  ->nullable()
                  ->comment('ملاحظات الدفع للشركة');
            
            // ===== بيانات إضافية مفيدة =====
            // تاريخ الاستحقاق المتوقع
            $table->date('payment_deadline')
                  ->nullable()
                  ->comment('تاريخ الاستحقاق المتوقع');
            
            // تاريخ المتابعة التالي
            $table->date('follow_up_date')
                  ->nullable()
                  ->comment('تاريخ المتابعة التالي');
            
            // مستوى الأولوية
            $table->enum('priority_level', ['low', 'medium', 'high'])
                  ->default('medium')
                  ->comment('مستوى أولوية المتابعة');
            
            // معلومات التتبع
            $table->unsignedBigInteger('last_updated_by')
                  ->nullable()
                  ->comment('آخر من قام بالتحديث');
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            
            // الطوابع الزمنية
            $table->timestamps();
            
            // الفهارس لتحسين الأداء
            $table->index('booking_id');
            $table->index('agent_payment_status');
            $table->index('company_payment_status');
            $table->index('payment_deadline');
            $table->index('follow_up_date');
        });
    }

    /**
     * التراجع عن الـ Migration
     */
    public function down()
    {
        Schema::dropIfExists('booking_financial_tracking');
    }
};

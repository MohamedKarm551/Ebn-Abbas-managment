<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الـ Migration - إنشاء جدول سجل تعديلات المصاريف الشهرية
     */
    public function up(): void
    {
        Schema::create('monthly_expense_logs', function (Blueprint $table) {
            $table->id(); // المفتاح الأساسي
            
            // ربط السجل بالمصروف الأصلي
            $table->foreignId('monthly_expense_id')
                  ->constrained('monthly_expenses')
                  ->onDelete('cascade'); // حذف السجلات عند حذف المصروف
            
            // معلومات المستخدم الذي قام بالتعديل
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // حذف السجلات عند حذف المستخدم
            
            // نوع العملية (إنشاء - تعديل - حذف)
            $table->enum('action_type', ['created', 'updated', 'deleted'])
                  ->comment('نوع العملية: إنشاء، تعديل، أو حذف');
            
            // اسم الحقل الذي تم تعديله
            $table->string('field_name', 100)
                  ->comment('اسم الحقل الذي تم تعديله');
            
            // التسمية الواضحة للحقل باللغة العربية
            $table->string('field_label', 150)
                  ->comment('التسمية الواضحة للحقل باللغة العربية');
            
            // القيمة القديمة (قبل التعديل)
            $table->text('old_value')
                  ->nullable()
                  ->comment('القيمة القديمة قبل التعديل');
            
            // القيمة الجديدة (بعد التعديل)
            $table->text('new_value')
                  ->nullable()
                  ->comment('القيمة الجديدة بعد التعديل');
            
            // العملة المرتبطة بالتعديل (إن وجدت)
            $table->string('currency', 5)
                  ->nullable()
                  ->comment('العملة المرتبطة بالحقل');
            
            // ملاحظات إضافية عن التعديل
            $table->text('notes')
                  ->nullable()
                  ->comment('ملاحظات إضافية عن التعديل');
            
            // عنوان IP الخاص بالمستخدم
            $table->string('ip_address', 45)
                  ->nullable()
                  ->comment('عنوان IP الخاص بالمستخدم');
            
            // معلومات المتصفح والجهاز
            $table->string('user_agent')
                  ->nullable()
                  ->comment('معلومات المتصفح والجهاز');
            
            // طوابع زمنية للإنشاء والتعديل
            $table->timestamps();
            
            // إضافة فهارس لتحسين الأداء
            $table->index(['monthly_expense_id', 'created_at']); // فهرس مركب للبحث السريع
            $table->index('user_id'); // فهرس للبحث بالمستخدم
            $table->index('action_type'); // فهرس لنوع العملية
            $table->index('field_name'); // فهرس لاسم الحقل
        });
    }

    /**
     * التراجع عن الـ Migration - حذف جدول سجل التعديلات
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_expense_logs');
    }
};

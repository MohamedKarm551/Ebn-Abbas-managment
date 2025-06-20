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
        Schema::create('admin_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id'); // ID الأدمن صاحب المعاملة
            $table->date('transaction_date')->default(now()); // تاريخ المعاملة
            $table->string('from_to')->nullable(); // من/إلى
            $table->decimal('amount', 15, 2)->nullable(); // القيمة
            $table->string('currency', 3)->default('SAR'); // العملة (SAR, KWD, EGP, USD)
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'other'])->default('other'); // نوع العملية
            $table->string('category')->nullable(); // التصنيف/الوسم
            $table->text('link_or_image')->nullable(); // رابط أو مسار الصورة
            $table->text('notes')->nullable(); // ملاحظات
            $table->decimal('exchange_rate', 10, 6)->nullable(); // سعر التحويل إذا تم استخدامه
            $table->string('base_currency', 3)->nullable(); // العملة الأساسية للتحويل
            $table->decimal('converted_amount', 15, 2)->nullable(); // المبلغ بعد التحويل
            $table->timestamps();

            // إضافة فهارس للأداء
            $table->index('admin_id');
            $table->index('transaction_date');
            $table->index('currency');
            $table->index('type');

            // ربط الجدول بجدول المستخدمين
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_transactions');
    }
};

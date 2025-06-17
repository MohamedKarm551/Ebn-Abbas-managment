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
        Schema::create('booking_report_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_operation_report_id')
                  ->constrained('booking_operation_reports')
                  ->onDelete('cascade');
            
            $table->enum('visa_type', ['سياحية', 'عمرة', 'زيارة', 'عمل'])->default('سياحية');
            $table->decimal('cost', 10, 2)->default(0); // التكلفة علينا
            $table->decimal('selling_price', 10, 2)->default(0); // سعر البيع
            $table->decimal('profit', 10, 2)->default(0); // الربح (محسوب تلقائياً)
            $table->string('currency')->default('KWD'); // العملة الافتراضية
            $table->integer('quantity')->default(1); // عدد التأشيرات
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_report_visas');
    }
};

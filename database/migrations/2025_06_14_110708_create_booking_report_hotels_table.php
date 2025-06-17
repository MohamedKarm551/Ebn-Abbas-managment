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
        Schema::create('booking_report_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_operation_report_id')
                  ->constrained('booking_operation_reports')
                  ->onDelete('cascade');
            
            // بيانات الفندق (مجلوبة من الحجز الأساسي)
            $table->string('hotel_name')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->integer('nights')->default(0); // عدد الليالي
            $table->integer('rooms')->default(1); // عدد الغرف
            
            // الأسعار والتكاليف
            $table->decimal('night_selling_price', 10, 2)->default(0); // سعر البيع لليلة الواحدة
            $table->decimal('total_selling_price', 10, 2)->default(0); // إجمالي سعر البيع
            $table->decimal('night_cost', 10, 2)->default(0); // تكلفة الليلة
            $table->decimal('total_cost', 10, 2)->default(0); // إجمالي التكلفة
            $table->decimal('profit', 10, 2)->default(0); // الربح من الفندق
            $table->string('currency')->default('KWD');
            $table->string('room_type')->nullable(); // نوع الغرفة
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_report_hotels');
    }
};

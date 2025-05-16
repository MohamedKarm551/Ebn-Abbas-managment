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
        Schema::create('land_trip_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_trip_id')->constrained('land_trips');
            $table->foreignId('land_trip_room_price_id')->constrained('land_trip_room_prices');
            $table->string('client_name');
            $table->foreignId('company_id')->constrained('companies');
            $table->integer('rooms')->default(1); // عدد الغرف المحجوزة
            $table->decimal('cost_price', 10, 2); // نسخة من سعر التكلفة وقت الحجز
            $table->decimal('sale_price', 10, 2); // نسخة من سعر البيع وقت الحجز
            $table->decimal('amount_due_to_agent', 10, 2); // المبلغ المستحق لجهة الحجز
            $table->decimal('amount_due_from_company', 10, 2); // المبلغ المستحق من الشركة
            $table->string('currency', 3)->default('SAR'); // رمز العملة
            $table->text('notes')->nullable(); // ملاحظات
            $table->foreignId('employee_id')->constrained('employees'); // الموظف المسؤول عن الحجز
            $table->timestamps();
            $table->softDeletes(); // للأرشفة بدل الحذف النهائي
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_trip_bookings');
    }
};

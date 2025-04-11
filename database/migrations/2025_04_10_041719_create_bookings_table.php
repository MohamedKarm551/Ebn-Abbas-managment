<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id(); // يتم إنشاء id تلقائيًا
            $table->string('client_name'); // اسم العميل
            $table->string('company_name'); // اسم الشركة
            $table->unsignedBigInteger('agent_id'); // جهة الحجز
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade'); // اسم الفندق
            $table->string('room_type'); // نوع الغرفة
            $table->date('check_in'); // تاريخ الدخول
            $table->date('check_out'); // تاريخ الخروج
            $table->integer('days'); // عدد أيام الحجز
            $table->integer('rooms'); // عدد الغرف
            $table->decimal('cost_price', 10, 2); // السعر من الفندق
            $table->decimal('amount_due_to_hotel', 15, 2); // يسمح بتخزين أرقام تصل إلى 999999999999.99
            $table->decimal('amount_paid_to_hotel', 10, 2)->default(0); // السداد مني للفندق
            $table->decimal('sale_price', 10, 2); // سعر البيع للشركة في الليلة
            $table->decimal('amount_due_from_company', 15, 2); // يسمح بتخزين أرقام تصل إلى 999999999999.99
            $table->decimal('amount_paid_by_company', 10, 2)->default(0); // السداد من الشركة
            $table->string('employee_name'); // الموظف المسؤول
            $table->string('payment_status')->default('unpaid'); // حالة الدفع
            $table->text('notes')->nullable(); // الملاحظات
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}

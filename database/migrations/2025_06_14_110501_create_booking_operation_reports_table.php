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
        Schema::create('booking_operation_reports', function (Blueprint $table) {
            $table->id();
            
            // تعديل السطر التالي للتأكد من أن نوع البيانات صحيح
            $table->unsignedBigInteger('employee_id')->nullable(); // تأكد من أنه unsignedBigInteger
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('set null');
            
            $table->date('report_date')->default(now());
            
            // بيانات العميل
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            
            // بيانات الشركة
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->string('company_name')->nullable();
            $table->string('company_phone')->nullable();
            
            // معلومات الحجز المرتبط
            $table->enum('booking_type', ['hotel', 'land_trip'])->nullable();
            $table->unsignedBigInteger('booking_id')->nullable(); // قد يكون booking أو land_trip_booking
            $table->string('booking_reference')->nullable(); // رقم مرجعي للحجز
            
            // الإجماليات المحسوبة
            $table->decimal('total_visa_profit', 12, 2)->default(0);
            $table->decimal('total_flight_profit', 12, 2)->default(0);
            $table->decimal('total_transport_profit', 12, 2)->default(0);
            $table->decimal('total_hotel_profit', 12, 2)->default(0);
            $table->decimal('total_land_trip_profit', 12, 2)->default(0);
            $table->decimal('grand_total_profit', 12, 2)->default(0);
            $table->string('currency')->default('KWD');
            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // فهارس
            $table->index(['employee_id', 'report_date']);
            $table->index(['client_id', 'company_id']);
            $table->index(['booking_type', 'booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_operation_reports');
    }
};

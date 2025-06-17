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
        Schema::create('booking_report_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_operation_report_id')
                  ->constrained('booking_operation_reports')
                  ->onDelete('cascade');
            
            $table->string('transport_type')->nullable(); // نوع النقل
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('vehicle_info')->nullable(); // معلومات المركبة
            
            $table->datetime('departure_time')->nullable();
            $table->datetime('arrival_time')->nullable();
            $table->text('schedule_notes')->nullable(); // ملاحظات المواعيد
            
            $table->string('ticket_file_path')->nullable(); // مرفق التذكرة
            
            $table->decimal('cost', 10, 2)->default(0); // تكلفة النقل
            $table->decimal('selling_price', 10, 2)->default(0); // سعر البيع
            $table->decimal('profit', 10, 2)->default(0); // الربح
            $table->string('currency')->default('KWD');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_report_transports');
    }
};

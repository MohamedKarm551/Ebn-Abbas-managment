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
        Schema::create('booking_report_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_operation_report_id')
                  ->constrained('booking_operation_reports')
                  ->onDelete('cascade');
            
            $table->date('flight_date')->nullable();
            $table->string('flight_number')->nullable();
            $table->string('airline')->nullable(); // شركة الطيران
            $table->string('route')->nullable(); // المسار (من - إلى)
            
            $table->decimal('cost', 10, 2)->default(0); // تكلفة الطيران
            $table->decimal('selling_price', 10, 2)->default(0); // سعر البيع
            $table->decimal('profit', 10, 2)->default(0); // الربح
            $table->string('currency')->default('KWD');
            $table->integer('passengers')->default(1); // عدد المسافرين
            $table->enum('trip_type', ['ذهاب فقط', 'ذهاب وعودة'])->default('ذهاب وعودة');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_report_flights');
    }
};

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
        Schema::create('booking_report_land_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_operation_report_id')
                  ->constrained('booking_operation_reports')
                  ->onDelete('cascade');
            
            // معلومات الرحلة
            $table->string('trip_type')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->integer('days')->default(1);
            
            // الأسعار والتكاليف
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->decimal('mecca_hotel_cost', 10, 2)->default(0);
            $table->decimal('medina_hotel_cost', 10, 2)->default(0);
            $table->decimal('extra_costs', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
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
        Schema::dropIfExists('booking_report_land_trips');
    }
};

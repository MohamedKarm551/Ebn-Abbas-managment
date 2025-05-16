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
        Schema::create('land_trips', function (Blueprint $table) {
            $table->id();
            $table->date('departure_date');
            $table->date('return_date');
            $table->integer('days_count');
            $table->foreignId('trip_type_id')->constrained('trip_types');
            $table->foreignId('hotel_id')->nullable()->constrained('hotels'); // إضافة حقل الفندق
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('agent_id')->constrained('agents'); // جهة الحجز
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_trips');
    }
};

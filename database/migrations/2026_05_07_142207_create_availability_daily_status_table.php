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
       Schema::create('availability_daily_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_room_type_id')->constrained('availability_room_types')->onDelete('cascade');
            $table->date('date'); // اليوم
            $table->integer('available_rooms')->default(0); // عدد الغرف المتاحة في اليوم ده
            $table->integer('booked_rooms')->default(0); // عدد الغرف المحجوزة
            $table->timestamps();
            
            // ضمان عدم تكرار نفس اليوم لنفس نوع الغرفة
            $table->unique(['availability_room_type_id', 'date']);
            
            // إضافة index للبحث السريع
            $table->index(['date', 'available_rooms', 'booked_rooms'], 'avail_daily_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_daily_status');
    }
};

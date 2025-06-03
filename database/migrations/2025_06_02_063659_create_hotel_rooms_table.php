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
        // إنشاء جدول للغرف
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('room_number');
            $table->string('floor')->nullable();
            $table->string('type')->default('standard'); // standard, suite, deluxe, etc.
            $table->string('status')->default('available'); // available, maintenance, reserved
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // مفتاح فريد لضمان عدم تكرار رقم الغرفة في نفس الفندق
            $table->unique(['hotel_id', 'room_number']);
        });
        
        // إنشاء جدول لتخصيص الغرف
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_assignments');
        Schema::dropIfExists('hotel_rooms');
    }
};

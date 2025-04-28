<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_room_types', function (Blueprint $table) {
            $table->id(); // آي دي الصف
            $table->foreignId('availability_id')->constrained()->onDelete('cascade'); // مربوط بجدول الإتاحات
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade'); // مربوط بجدول أنواع الغرف
            $table->decimal('cost_price', 10, 2); // سعر التكلفة (من جهة الحجز) لنوع الغرفة ده
            $table->decimal('sale_price', 10, 2); // سعر البيع (للشركة) لنوع الغرفة ده
            $table->integer('allotment')->nullable(); // اختياري: عدد الغرف المتاحة من النوع ده في الإتاحة دي
            $table->timestamps(); // created_at و updated_at
            // ضمان عدم تكرار نفس نوع الغرفة لنفس الإتاحة
            $table->unique(['availability_id', 'room_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_room_types');
    }
};

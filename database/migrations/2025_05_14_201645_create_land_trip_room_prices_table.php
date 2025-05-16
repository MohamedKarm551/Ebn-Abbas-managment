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
        Schema::create('land_trip_room_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_trip_id')->constrained('land_trips')->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained('room_types');
            $table->decimal('cost_price', 10, 2); // سعر التكلفة
            $table->decimal('sale_price', 10, 2); // سعر البيع للشركة
            $table->integer('allotment')->nullable(); // عدد الغرف المتاحة (اختياري)
            $table->timestamps();

            // ضمان عدم تكرار نفس نوع الغرفة للرحلة الواحدة
            $table->unique(['land_trip_id', 'room_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_trip_room_prices');
    }
};

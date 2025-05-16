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
        Schema::create('land_trip_booking_room_price', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('land_trip_room_price_id');
            $table->unsignedBigInteger('booking_id');
            $table->integer('rooms')->default(1);
            $table->timestamps();
            
            $table->foreign('land_trip_room_price_id')->references('id')->on('land_trip_room_prices')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('land_trip_bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_trip_booking_room_price');
    }
};

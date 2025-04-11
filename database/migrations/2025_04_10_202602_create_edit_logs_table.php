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
        Schema::create('edit_logs', function (Blueprint $table) {
            $table->id(); // العمود الأساسي (Primary Key)
            $table->unsignedBigInteger('booking_id'); // ربط التعديل بالحجز
            $table->string('field'); // اسم الحقل المعدل
            $table->text('old_value')->nullable(); // القيمة القديمة للحقل
            $table->text('new_value'); // القيمة الجديدة للحقل
            $table->timestamps(); // الأعمدة created_at و updated_at
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade'); // الربط بالحجز وحذف التعديلات عند حذف الحجز
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edit_logs');
    }
};

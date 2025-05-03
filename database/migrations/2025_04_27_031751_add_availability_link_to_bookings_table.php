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
        Schema::table('bookings', function (Blueprint $table) {
            // ربط الحجز بصف الإتاحة ونوع الغرفة المحدد اللي جه منه الحجز            // ممكن يكون فاضي لو الحجز مش جاي من إتاحة
            $table->foreignId('availability_room_type_id')
                  ->nullable() // يسمح بالقيمة الفارغة
                  ->constrained('availability_room_types') // مربوط بجدول availability_room_types
                  ->onDelete('set null'); // لو صف الإتاحة/الغرفة اتحذف، قيمة العمود ده في الحجز تبقى null (عشان منمسحش سجل الحجز)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // *** بداية التعديل: استخدام اسم القيد الصريح ***
            // اسم القيد ده جبناه من رسالة الخطأ
            $table->dropForeign('bookings_availability_room_type_id_foreign');

            // بعد ما حذفنا القيد، نقدر نحذف العمود
            $table->dropColumn('availability_room_type_id');
        });
    }
};

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
        Schema::table('hotels', function (Blueprint $table) {
            // مسار صورة الفندق (ممكن يكون فاضي) - هيتحط بعد عمود location
            $table->string('image_path')->nullable()->after('location');
            // وصف الفندق (المرافق، إلخ) (ممكن يكون فاضي) - هيتحط بعد عمود image_path
            $table->text('description')->nullable()->after('image_path');
            // اتأكد إن عمود 'location' موجود ومناسب، أو ضيف أعمدة للمدينة/المنطقة لو محتاج
            // العمود الحالي 'location' ممكن يكون كافي بناءً على c:\xampp\htdocs\Ebn-Abbas-managment\database\migrations\2025_04_10_041710_create_hotels_table.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'description']);
        });
    }
};

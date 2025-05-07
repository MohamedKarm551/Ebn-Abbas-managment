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
            if (Schema::hasColumn('hotels', 'image_path')) { // تحقق من وجود العمود قبل محاولة حذفه
                $table->dropColumn('image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // إعادة إضافة العمود إذا تم التراجع عن الـ migration
            $table->string('image_path')->nullable()->after('location');
        });
    }
};

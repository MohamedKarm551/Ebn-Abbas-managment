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
            // إضافة حقل لعدد الغرف المشتراة، مع قيمة افتراضية 30
            // يمكنك تغيير مكان الحقل باستخدام ->after('column_name')
            $table->integer('purchased_rooms_count')->default(30)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('purchased_rooms_count');
        });
    }
};

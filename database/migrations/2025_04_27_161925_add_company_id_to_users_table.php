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
        Schema::table('users', function (Blueprint $table) {
            // نضيف العمود بعد عمود 'id' مثلاً
            // نجعله nullable عشان مش كل المستخدمين (زي الأدمن) هيكونوا تابعين لشركة
            // onDelete('set null') يعني لو الشركة اتحذفت، قيمة company_id للمستخدمين دول هتبقى null
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // نحذف الـ foreign key الأول وبعدين العمود
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};

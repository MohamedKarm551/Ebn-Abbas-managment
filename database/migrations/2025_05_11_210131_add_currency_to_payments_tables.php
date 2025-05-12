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
        // إضافة حقل العملة لجدول دفعات الشركات
        Schema::table('payments', function (Blueprint $table) {
            $table->string('currency')->default('SAR')->after('amount');
        });

        // إضافة حقل العملة لجدول دفعات الوكلاء
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->string('currency')->default('SAR')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
        
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};

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
         Schema::table('availability_room_types', function (Blueprint $table) {
        $table->string('currency', 10)->default('SAR')->after('sale_price');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availability_room_types', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};

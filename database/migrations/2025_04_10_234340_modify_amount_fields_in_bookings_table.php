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
            $table->decimal('amount_due_to_hotel', 10, 2)->nullable()->change(); // جعل الحقل قابلًا لأن يكون فارغًا
            $table->decimal('amount_due_from_company', 10, 2)->nullable()->change(); // جعل الحقل قابلًا لأن يكون فارغًا
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('amount_due_to_hotel', 10, 2)->nullable(false)->change();
            $table->decimal('amount_due_from_company', 10, 2)->nullable(false)->change();
        });
    }
};

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
        Schema::create('allotment_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allotment_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('rooms_sold');
            $table->decimal('sale_price', 10, 2);
            $table->string('currency', 5)->default('SAR');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allotment_sales');
    }
};

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
        Schema::create('masr_expense_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('masr_expense_id');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('EGP');
            $table->timestamps();

            $table->foreign('masr_expense_id')->references('id')->on('masr_expenses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masr_expense_items');
    }
};

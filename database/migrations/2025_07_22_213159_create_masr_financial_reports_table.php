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
        Schema::create('masr_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_by');
             $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masr_financial_reports');
    }
};

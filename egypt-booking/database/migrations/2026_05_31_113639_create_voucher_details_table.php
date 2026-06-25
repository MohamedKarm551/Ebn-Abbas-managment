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
        Schema::create('voucher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->string('voucher_type');
            $table->foreignId('debit_account_id')->constrained('accounts');
            $table->foreignId('credit_account_id')->constrained('accounts');
            $table->decimal('amount', 15, 2);
            $table->string('subject')->nullable();          
            $table->date('cheque_date')->nullable();
            $table->string('sig_receiver')->nullable();
            $table->string('sig_accountant')->nullable();
            $table->string('sig_manager')->nullable();
             $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_details');
    }
};

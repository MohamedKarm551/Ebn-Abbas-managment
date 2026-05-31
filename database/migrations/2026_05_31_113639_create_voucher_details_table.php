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
            $table->foreignId('journal_entry_id')
                  ->constrained('journal_entries')
                  ->cascadeOnDelete();
            $table->enum('voucher_type', ['receipt', 'payment']);
            $table->foreignId('debit_account_id')->constrained('accounts');
            $table->foreignId('credit_account_id')->constrained('accounts');
            $table->decimal('amount', 15, 2);
            $table->string('subject')->nullable();          // وذلك عن
            $table->string('description')->nullable();      // البيان الداخلي
            $table->enum('payment_method', ['cash','cheque'])->default('cash');
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('sig_receiver')->nullable();
            $table->string('sig_accountant')->nullable();
            $table->string('sig_manager')->nullable();
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

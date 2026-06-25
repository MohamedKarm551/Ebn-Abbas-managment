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
        Schema::table('payments', function (Blueprint $table) {
             $table->foreignId('journal_entry_id')
              ->nullable()
              ->constrained('journal_entries')
              ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
             $table->dropForeign(['journal_entry_id']);
             $table->dropColumn('journal_entry_id');
        });
    }
};

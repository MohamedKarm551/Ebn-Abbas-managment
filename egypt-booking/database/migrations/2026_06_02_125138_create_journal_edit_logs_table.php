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
        Schema::create('journal_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')
                  ->constrained('journal_entries')
                  ->onDelete('cascade');
            $table->foreignId('user_id')->nullable()
                  ->constrained('users');
            $table->string('action'); // edit, delete, create
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['journal_entry_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_edit_logs');
    }
};
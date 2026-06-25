<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    // جدول الحسابات
    Schema::create('accounts', function (Blueprint $table) {
        $table->id();
        $table->string('code', 20)->unique();
        $table->string('name');
        $table->enum('type', ['asset','liability','equity','revenue','expense']);
        $table->enum('normal_balance', ['debit','credit']);
        $table->foreignId('parent_id')->nullable()
              ->constrained('accounts')->onDelete('restrict');
        $table->integer('level')->default(1);
        $table->boolean('is_leaf')->default(true);
        $table->boolean('is_active')->default(true);
        $table->text('description')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    // القيود المحاسبية
    Schema::create('journal_entries', function (Blueprint $table) {
        $table->id();
        $table->string('reference');
        $table->date('entry_date');
        $table->enum('status', ['draft','posted'])->default('draft');
        $table->string('source_type')->nullable(); // Booking, Payment, Discount
        $table->unsignedBigInteger('source_id')->nullable();
        $table->foreignId('created_by')->constrained('users');
        $table->timestamps();
        $table->softDeletes();
        $table->index('reference');
        $table->index('entry_date');
    });

    // أسطر القيد
    Schema::create('journal_entry_lines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('journal_entry_id')
              ->constrained()->onDelete('cascade');
        $table->foreignId('account_id')->constrained('accounts');
        $table->decimal('debit', 15, 2)->default(0);
        $table->decimal('credit', 15, 2)->default(0);
        $table->string('description')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    // دفتر الأستاذ
    Schema::create('account_ledgers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('account_id')
              ->constrained('accounts')->onDelete('cascade');
        $table->foreignId('journal_entry_id')
              ->constrained('journal_entries');
        $table->decimal('debit', 15, 2)->default(0);
        $table->decimal('credit', 15, 2)->default(0);
        $table->decimal('running_balance', 15, 2)->default(0);
        $table->text('description')->nullable();
        $table->timestamps();
        $table->index(['account_id', 'created_at']);
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('account_ledgers');
    Schema::dropIfExists('journal_entry_lines');
    Schema::dropIfExists('journal_entries');
    Schema::dropIfExists('accounts');
}
};

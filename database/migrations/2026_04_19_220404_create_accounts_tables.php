<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();           // 1, 1.1, 1.1.1
            $table->string('name');                         // اسم الحساب
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('restrict');
            $table->integer('level')->default(1);
            $table->boolean('is_leaf')->default(true);      // هل ينفع يتربط بقيود؟
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // جدول القيود المحاسبية
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->date('entry_date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->string('source_type')->nullable(); // booking, payment, expense ...
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('created_by')->constrained('users');
             $table->foreignId('updated_by')->nullable()->constrained('users'); 
            $table->foreignId('deleted_by')->nullable()->constrained('users'); 
            $table->timestamps();
            $table->softDeletes();

            $table->index('reference');
            $table->index('entry_date');
            $table->index('created_by');
            $table->index('status');
        });

        // أسطر القيد
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts');
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // جدول سجل التعديلات (Audit Log)
        Schema::create('journal_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('action');           // edit, delete, restore, approve
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['journal_entry_id', 'action']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('journal_edit_logs');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
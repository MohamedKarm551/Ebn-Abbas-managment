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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id(); // آي دي الإتاحة
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade'); // مربوط بجدول الفنادق (لو الفندق اتحذف، الإتاحة تتحذف)
            $table->foreignId('agent_id')->constrained()->onDelete('cascade'); // مربوط بجدول جهات الحجز (لو جهة الحجز اتحذفت، الإتاحة تتحذف)
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // مربوط بجدول الموظفين (الموظف اللي ضاف الإتاحة)
            $table->date('start_date'); // تاريخ بداية الإتاحة
            $table->date('end_date'); // تاريخ نهاية الإتاحة
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active'); // حالة الإتاحة (نشطة، غير نشطة، منتهية)
            $table->text('notes')->nullable(); // ملاحظات عامة للاستخدام الداخلي
            $table->timestamps(); // created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};

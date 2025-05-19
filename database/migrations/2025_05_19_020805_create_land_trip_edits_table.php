<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
           Schema::create('land_trip_edits', function (Blueprint $table) {
        $table->id();
        $table->foreignId('land_trip_id')->constrained()->onDelete('cascade'); // الرحلة المرتبطة
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // اللي عدل
        $table->string('field'); // اسم الحقل اللي اتعدل
        $table->text('old_value')->nullable(); // القيمة القديمة
        $table->text('new_value')->nullable(); // القيمة الجديدة
        $table->timestamps(); // created_at = وقت التعديل
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('land_trip_edits');
    }
};

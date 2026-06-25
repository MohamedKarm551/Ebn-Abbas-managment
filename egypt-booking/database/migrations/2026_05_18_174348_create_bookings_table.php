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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('restrict');
            $table->string('client_name');
            $table->enum('gender', ['male', 'female', 'child', 'infant']);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('passport_image');
            $table->string('personal_photo');
            $table->enum('accommodation_type', [
            'فردية', 'ثنائية', 'ثلاثية', 'رباعية', 'خماسية', 'سداسية', 'طفل', 'رضيع'
            ]);
            $table->decimal('base_price', 10, 2)->default(0);
            $table->foreignId('representative_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsTable extends Migration
{
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id(); // تأكد من أن العمود id هو bigIncrements
            $table->string('name'); // اسم جهة الحجز
            $table->string('color')->default('#0000'); // لون الخلفية الافتراضي
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agents');
    }
}
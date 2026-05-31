<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('agent_payments', function (Blueprint $table) {
        $table->foreignId('account_id')->nullable()->after('currency')->constrained('accounts')->nullOnDelete();
    });
}

public function down()
{
    Schema::table('agent_payments', function (Blueprint $table) {
        $table->dropForeign(['account_id']);
        $table->dropColumn('account_id');
    });
}
};

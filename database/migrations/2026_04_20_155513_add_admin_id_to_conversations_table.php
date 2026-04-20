<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('conversations', function (Blueprint $table) {
        $table->unsignedBigInteger('admin_id')->nullable()->after('user_id');

        $table->foreign('admin_id')
              ->references('id')
              ->on('users')
              ->onDelete('set null');
    });
}

public function down()
{
    Schema::table('conversations', function (Blueprint $table) {
        $table->dropForeign(['admin_id']);
        $table->dropColumn('admin_id');
    });
}
};

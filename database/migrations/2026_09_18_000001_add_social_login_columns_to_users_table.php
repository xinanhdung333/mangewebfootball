<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('facebook_id')->nullable()->unique()->after('google_id');
            $table->string('phone', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_google_id_unique');
            $table->dropUnique('users_facebook_id_unique');
            $table->dropColumn(['google_id', 'facebook_id']);
            $table->string('phone', 20)->nullable(false)->change();
        });
    }
};

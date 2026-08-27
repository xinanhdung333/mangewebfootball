<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->text('admin_reply')->nullable()->after('message');
            $table->integer('replied_by')->nullable()->after('admin_reply')->index('idx_feedback_replied_by');
            $table->dateTime('replied_at')->nullable()->after('replied_by');
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropIndex('idx_feedback_replied_by');
            $table->dropColumn(['admin_reply', 'replied_by', 'replied_at']);
        });
    }
};
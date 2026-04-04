<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table): void {
            $table->string('subject_type', 160)->nullable()->after('notifiable_id');
            $table->string('subject_id', 36)->nullable()->after('subject_type');

            $table->index(['subject_type', 'subject_id'], 'notification_logs_subject_index');
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table): void {
            $table->dropIndex('notification_logs_subject_index');
            $table->dropColumn(['subject_type', 'subject_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            // nullable — null means attached to ticket creation, set means attached to a comment
            $table->foreignId('comment_id')
                  ->nullable()
                  ->after('ticket_id')
                  ->constrained('comments')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Comment::class);
            $table->dropColumn('comment_id');
        });
    }
};

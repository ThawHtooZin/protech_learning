<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('forum_posts')->cascadeOnDelete();
            $table->index(['forum_thread_id', 'parent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropIndex(['forum_thread_id', 'parent_id', 'created_at']);
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_post_id')->constrained('team_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->timestamps();

            $table->unique(['team_post_id', 'user_id', 'emoji']);
            $table->index(['team_post_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_post_reactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('slug', 80);
            $table->boolean('is_general')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'sort_order']);
        });

        $now = now();
        $teamIds = DB::table('teams')->pluck('id');
        foreach ($teamIds as $teamId) {
            DB::table('team_channels')->insert([
                'team_id' => $teamId,
                'name' => 'General',
                'slug' => 'general',
                'is_general' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_channels');
    }
};

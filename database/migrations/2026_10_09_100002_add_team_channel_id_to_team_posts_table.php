<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_posts', function (Blueprint $table) {
            $table->foreignId('team_channel_id')
                ->nullable()
                ->after('team_id')
                ->constrained('team_channels')
                ->cascadeOnDelete();
        });

        $generals = DB::table('team_channels')
            ->where('is_general', true)
            ->pluck('id', 'team_id');

        foreach ($generals as $teamId => $channelId) {
            DB::table('team_posts')
                ->where('team_id', $teamId)
                ->whereNull('team_channel_id')
                ->update(['team_channel_id' => $channelId]);
        }
    }

    public function down(): void
    {
        Schema::table('team_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_channel_id');
        });
    }
};

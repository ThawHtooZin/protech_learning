<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_posts', function (Blueprint $table) {
            $table->string('subject', 200)->nullable()->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('team_posts', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }
};

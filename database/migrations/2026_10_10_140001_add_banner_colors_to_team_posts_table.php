<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_posts', function (Blueprint $table) {
            $table->string('banner_bg', 7)->nullable()->after('subject');
            $table->string('banner_text', 7)->nullable()->after('banner_bg');
            $table->boolean('body_is_html')->default(false)->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('team_posts', function (Blueprint $table) {
            $table->dropColumn(['banner_bg', 'banner_text', 'body_is_html']);
        });
    }
};

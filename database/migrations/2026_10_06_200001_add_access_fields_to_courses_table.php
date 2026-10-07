<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('access_type', 20)->default('private')->after('is_published');
            $table->unsignedInteger('price_mmk')->nullable()->after('access_type');
            $table->boolean('is_listed')->default(true)->after('price_mmk');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'price_mmk', 'is_listed']);
        });
    }
};

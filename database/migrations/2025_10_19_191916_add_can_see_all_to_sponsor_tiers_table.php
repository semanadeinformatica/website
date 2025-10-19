<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_tiers', function (Blueprint $table) {
            $table->boolean('canSeeAll')->default(false)->after('canSeeLinkedin');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_tiers', function (Blueprint $table) {
            $table->dropColumn('canSeeAll');
        });
    }
};

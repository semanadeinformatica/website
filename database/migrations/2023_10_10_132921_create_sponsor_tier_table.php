<?php

use App\Models\Edition;
use App\Models\SponsorTier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sponsor_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#000000');
            $table->integer('rank')->unsigned();

            // TODO: This should be some kind of "Roles and Permissions" system but I'm tired - Nuno Pereira
            $table->boolean('canSeeCV');
            $table->boolean('canSeeLinkedin');

            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn('tier');
            $table->foreignIdFor(SponsorTier::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(SponsorTier::class);
            $table->enum('tier', ['PLATINUM', 'GOLD', 'SILVER']);
        });

        Schema::dropIfExists('sponsor_tiers');
    }
};

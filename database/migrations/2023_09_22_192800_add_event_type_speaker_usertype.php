<?php

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function DatabaseHelpers\createManyToManyRelation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignIdFor(EventType::class)->constrained()->cascadeOnDelete();
        });

        Schema::table('speakers', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->unique()->constrained()->cascadeOnDelete();
            $table->dropColumn('name');
            $table->dropColumn('profile_photo_path');
            $table->dropConstrainedForeignIdFor(Event::class);
        });

        createManyToManyRelation(Event::class, User::class);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('event_user');

        Schema::table('speakers', function (Blueprint $table) {
            $table->string('name');
            $table->string('profile_photo_path', 2048)->nullable();
            $table->foreignIdFor(Event::class)->constrained()->cascadeOnDelete();
            $table->dropConstrainedForeignIdFor(User::class);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(EventType::class);
        });

        Schema::dropIfExists('event_types');
    }
};

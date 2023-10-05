<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Socials
        Schema::create('social_media', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('facebook')->nullable();
            $table->string('github')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        // User tables
        Schema::table('users', function (Blueprint $table) {
            $table->morphs('usertype');
        });
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->unique()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\SocialMedia::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['PLATINUM', 'GOLD', 'SILVER']);
            $table->foreignIdFor(\App\Models\User::class)->unique()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\SocialMedia::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // SINF tables
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('year');
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price')->unsigned();
            $table->integer('stock')->unsigned();
            $table->foreignIdFor(\App\Models\Edition::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Activity tables
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->string('topic');
            $table->integer('capacity')->unsigned()->nullable();
            $table->foreignIdFor(\App\Models\Edition::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('organization')->nullable();
            $table->foreignIdFor(\App\Models\SocialMedia::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Event::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Quest tables
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('points')->unsigned();

            // we need to be careful to update this when we add new categories
            $table->enum('category', ['COMPANY', 'TALK', 'WORKSHOP', 'MILESTONE', 'TEAMBUILDING']);

            $table->morphs('requirement');

            $table->foreignIdFor(\App\Models\Edition::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Enrollment
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Participant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Edition::class)->constrained()->cascadeOnDelete();

            // this will be calculated by a trigger
            $table->integer('points')->unsigned()->default(0);
            $table->timestamps();
            $table->unique(['participant_id', 'edition_id']);
        });

        // Many to many
        Schema::create('enrollment_event', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Enrollment::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Event::class)->constrained()->cascadeOnDelete();
            $table->primary(['enrollment_id', 'event_id']);
        });
        Schema::create('enrollment_quest', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Enrollment::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Quest::class)->constrained()->cascadeOnDelete();
            $table->primary(['enrollment_id', 'quest_id']);
        });
        Schema::create('enrollment_product', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Enrollment::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Product::class)->constrained()->cascadeOnDelete();
            $table->primary(['enrollment_id', 'product_id']);
        });

        // Triggers
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_enrollment_points() RETURNS TRIGGER AS $$
                BEGIN
                    IF (OLD IS NOT NULL) THEN
                        UPDATE enrollments SET points = points - (
                            SELECT points FROM quests WHERE id = OLD.quest_id
                        ) WHERE id = OLD.enrollment_id;
                    END IF;

                    IF (NEW IS NOT NULL) THEN
                        UPDATE enrollments SET points = points + (
                            SELECT points FROM quests WHERE id = NEW.quest_id
                        ) WHERE id = NEW.enrollment_id;
                    END IF;

                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');
        DB::unprepared('
            CREATE TRIGGER update_enrollment_points
            AFTER INSERT OR UPDATE OR DELETE ON enrollment_quest
            FOR EACH ROW EXECUTE FUNCTION update_enrollment_points();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_enrollment_points ON enrollment_quest;');
        DB::unprepared('DROP FUNCTION IF EXISTS update_enrollment_points;');
        Schema::dropIfExists('enrollment_product');
        Schema::dropIfExists('enrollment_quest');
        Schema::dropIfExists('enrollment_event');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('events');
        Schema::dropIfExists('products');
        Schema::dropIfExists('editions');

        Schema::dropIfExists('admins');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('participants');

        Schema::table('users', function (Blueprint $table) {
            $table->dropMorphs('usertype');
        });

        Schema::dropIfExists('social_media');
    }
};

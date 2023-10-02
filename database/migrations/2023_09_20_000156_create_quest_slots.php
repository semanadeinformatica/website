<?php

use App\Models\Enrollment;
use App\Models\Quest;
use App\Models\Slot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function DatabaseHelpers\createManyToManyRelation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_enrollment_points ON enrollment_quest;');
        DB::unprepared('DROP FUNCTION IF EXISTS update_enrollment_points;');

        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('points');
        });

        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->integer('total_quests')->unsigned()->nullable()->default(1);
            $table->integer('points')->unsigned();
            $table->timestamps();
        });

        // Total requirements per slot
        createManyToManyRelation(Slot::class, Quest::class);

        // "Sticker book"
        createManyToManyRelation(Slot::class, Enrollment::class);

        //Triggers
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_quest_slots() RETURNS TRIGGER AS $$
                DECLARE
                    slot RECORD;
                BEGIN
                    IF (NEW IS NOT NULL) THEN
                        FOR slot IN
                            SELECT s.*
                            FROM slots s
                            LEFT JOIN quest_slot qs ON qs.slot_id = s.id 
                            WHERE qs.quest_id = NEW.quest_id
                        LOOP
                            -- Skip over already unlocked slots
                            CONTINUE WHEN EXISTS (
                                SELECT 1 
                                FROM enrollment_slot es
                                WHERE es.slot_id = slot.id AND es.enrollment_id = NEW.enrollment_id
                            );

                            IF (
                                SELECT count(*)
                                FROM slots s
                                LEFT JOIN quest_slot qs ON qs.slot_id = s.id
                                LEFT JOIN enrollment_quest eq ON eq.quest_id = qs.quest_id
                                WHERE s.id = slot.id AND eq.enrollment_id = NEW.enrollment_id
                            ) >= slot.total_quests
                            THEN
                                INSERT INTO enrollment_slot (enrollment_id, slot_id)
                                VALUES (NEW.enrollment_id, slot.id);
                            END IF;
                        END LOOP;
                    END IF;
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');

        DB::unprepared('
            CREATE TRIGGER update_enrollment_quest_history
            AFTER INSERT ON enrollment_quest
            FOR EACH ROW EXECUTE FUNCTION update_quest_slots();
        ');

        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_enrollment_points() RETURNS TRIGGER AS $$
                BEGIN
                    IF (OLD IS NOT NULL) THEN
                        UPDATE enrollments SET points = points - (
                            SELECT points FROM slots WHERE id = OLD.slot_id
                        ) WHERE id = OLD.enrollment_id;
                    END IF;

                    IF (NEW IS NOT NULL) THEN
                        UPDATE enrollments SET points = points + (
                            SELECT points FROM slots WHERE id = NEW.slot_id
                        ) WHERE id = NEW.enrollment_id;
                    END IF;

                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');
        DB::unprepared('
            CREATE TRIGGER update_enrollment_slot_history
            AFTER INSERT OR UPDATE OR DELETE ON enrollment_slot
            FOR EACH ROW EXECUTE FUNCTION update_enrollment_points();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::unprepared('DROP TRIGGER IF EXISTS update_enrollment_slot_history ON enrollment_slot;');
        DB::unprepared('DROP FUNCTION IF EXISTS update_enrollment_points;');

        DB::unprepared('DROP TRIGGER IF EXISTS update_enrollment_quest_history ON enrollment_quest;');
        DB::unprepared('DROP FUNCTION IF EXISTS update_quest_slots;');

        Schema::dropIfExists('enrollment_slot');
        Schema::dropIfExists('quest_slot');
        Schema::dropIfExists('slots');

        Schema::table('quests', function (Blueprint $table) {
            $table->integer('points')->unsigned();
        });

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

        // Visit history
        Schema::create('visit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Participant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Company::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }
};

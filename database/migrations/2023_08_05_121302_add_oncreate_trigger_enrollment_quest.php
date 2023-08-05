<?php

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
        // Visit history
        Schema::create('visit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Student::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Company::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        //Triggers
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_visit_history() RETURNS TRIGGER AS $$
                BEGIN
                    DECLARE v_company_tier TEXT;
                    DECLARE v_company_id INT;
                    BEGIN
                        IF (NEW IS NOT NULL) THEN

                            SELECT c.tier
                            INTO v_company_tier
                            FROM quests q
                            LEFT JOIN companies c ON q.requirement_type = \'\App\Models\Company\' AND q.requirement_id = c.id
                            WHERE q.id = NEW.quest_id;

                            IF ((v_company_tier != \'SILVER\') AND (SELECT requirement_type FROM quests WHERE id = NEW.quest_id) = \'\App\Models\Company\')THEN

                                SELECT requirement_id INTO v_company_id FROM quests WHERE id = NEW.quest_id;

                                INSERT INTO visit_history (student_id, company_id, created_at, updated_at)
                                SELECT
                                    e.student_id,
                                    v_company_id,
                                    NOW(),
                                    NOW()
                                FROM enrollments e
                                LEFT JOIN students s ON e.student_id = s.id
                                LEFT JOIN users u ON s.user_id = u.id
                                WHERE e.id = NEW.enrollment_id;
                            END IF;
                        END IF;
                    END;
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;

    ');

        DB::unprepared('
            CREATE TRIGGER update_visit_history
            AFTER INSERT ON enrollment_quest
            FOR EACH ROW EXECUTE FUNCTION update_visit_history();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_visit_history');
        Schema::dropIfExists('visit_history');
    }
};

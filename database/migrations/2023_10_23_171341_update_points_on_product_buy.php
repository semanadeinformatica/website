<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_enrollment_points_on_product_buy() RETURNS TRIGGER AS $$
                BEGIN
                    IF (NEW IS NOT NULL) THEN
                        UPDATE enrollments SET points = points - (
                            SELECT price FROM products WHERE id = NEW.product_id
                        ) WHERE id = NEW.enrollment_id;
                    END IF;

                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        ');
        DB::unprepared('
            CREATE TRIGGER update_enrollment_points_on_product_buy_trigger
            AFTER INSERT ON enrollment_product
            FOR EACH ROW EXECUTE FUNCTION update_enrollment_points_on_product_buy();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_enrollment_points_on_product_buy_trigger ON enrollment_product;');
        DB::unprepared('DROP FUNCTION IF EXISTS update_enrollment_points_on_product_buy;');
    }
};

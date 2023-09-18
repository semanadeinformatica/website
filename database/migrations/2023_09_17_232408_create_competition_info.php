<?php

use App\Models\Edition;
use App\Models\Student;
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
        Schema::create('competition_info', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Student::class, 'first_place')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Student::class, 'second_place')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Student::class, 'third_place')->constrained()->cascadeOnDelete();
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->text('theme');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_info');
    }
};

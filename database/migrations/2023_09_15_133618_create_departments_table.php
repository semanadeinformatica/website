<?php

use App\Models\Department;
use App\Models\Edition;
use App\Models\Participant;
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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->unique(['name', 'edition_id']);
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->boolean('coordinator');
            $table->foreignIdFor(Participant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Department::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
        Schema::dropIfExists('departments');
    }
};

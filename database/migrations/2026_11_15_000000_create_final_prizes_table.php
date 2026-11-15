<?php

use App\Models\Edition;
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
        Schema::create('final_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('place');
            $table->string('title');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->unique(['edition_id', 'place']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_prizes');
    }
};

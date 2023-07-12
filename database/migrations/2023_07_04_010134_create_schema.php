<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // User tables
        Schema::table('users', function (Blueprint $table) {
            $table->morphs('usertype');
        });
        Schema::create('students', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class)->primary();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class)->primary();
            $table->timestamps();
        });
        Schema::create('admins', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\User::class)->primary();
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
            $table->foreignIdFor(\App\Models\Edition::class);
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
            $table->foreignIdFor(\App\Models\Edition::class);
            $table->timestamps();
        });
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('organization')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->foreignIdFor(\App\Models\Event::class);
            $table->timestamps();
        });

        // Quest tables
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('points')->unsigned();

            // we need to be careful to update this when we add new categories
            $table->enum('category', ['COMPANY', 'TALK', 'WORKSHOP', 'MILESTONE', 'TEAMBUILDING']);

            // TODO: Unlock condition
            $table->foreignIdFor(\App\Models\Edition::class);
            $table->timestamps();
        });

        // Enrollment
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Student::class);
            $table->foreignIdFor(\App\Models\Edition::class);

            // this will be calculated by a trigger
            $table->integer('points')->unsigned()->default(0);
            $table->timestamps();
            $table->unique(['student_id', 'edition_id']);
        });

        // Many to many
        Schema::create('enrollment_event', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Enrollment::class);
            $table->foreignIdFor(\App\Models\Event::class);
            $table->primary(['enrollment_id', 'event_id']);
        });
        Schema::create('enrollment_quest', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Enrollment::class);
            $table->foreignIdFor(\App\Models\Quest::class);
            $table->primary(['enrollment_id', 'quest_id']);
        });
        Schema::create('enrollment_product', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Enrollment::class);
            $table->foreignIdFor(\App\Models\Product::class);
            $table->primary(['enrollment_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropMorphs('usertype');
        });
        Schema::dropIfExists('students');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('editions');
        Schema::dropIfExists('products');
        Schema::dropIfExists('events');
        Schema::dropIfExists('speakers');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('enrollment_event');
        Schema::dropIfExists('enrollment_quest');
        Schema::dropIfExists('enrollment_product');
    }
};
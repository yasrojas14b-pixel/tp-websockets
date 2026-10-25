<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * business_hours: each row is one working range for a weekday
     * (0 = Sunday ... 6 = Saturday). A day with a lunch break has two rows,
     * e.g. 09:00-13:00 and 14:00-19:00.
     *
     * schedule_blocks: one-off unavailable periods (vacations, errands, a
     * long break on a specific day).
     */
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('weekday')->index();
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();
        });

        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_blocks');
        Schema::dropIfExists('business_hours');
    }
};

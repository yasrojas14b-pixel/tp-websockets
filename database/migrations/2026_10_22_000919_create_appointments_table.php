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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_code')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('pending');
            $table->decimal('total_price', 10, 2);
            $table->string('source', 20)->default('online');
            $table->text('client_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
        });

        /*
         * Price and duration are copied at booking time so later catalog
         * changes do not alter past appointments.
         */
        Schema::create('addon_appointment', function (Blueprint $table) {
            $table->foreignId('addon_id')->constrained()->restrictOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('duration_minutes');

            $table->primary(['addon_id', 'appointment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_appointment');
        Schema::dropIfExists('appointments');
    }
};

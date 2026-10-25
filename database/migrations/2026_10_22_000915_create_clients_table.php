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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->unique();
            $table->string('email')->nullable();
            $table->string('nail_type')->nullable();
            $table->text('allergies')->nullable();
            $table->text('preferred_designs')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('client_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_photos');
        Schema::dropIfExists('clients');
    }
};

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
        Schema::create('race_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('race_id')->constrained('races', 'race_id');
            $table->integer('dnf_count')->nullable();
            $table->foreignId('fastest_lap_driver_id')->nullable()->constrained('drivers', 'driver_id');
            $table->integer('points')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            
            // Cada usuario solo puede tener una predicción por carrera
            $table->unique(['user_id', 'race_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_predictions');
    }
};

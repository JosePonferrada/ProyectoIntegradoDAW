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
        Schema::create('prediction_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_prediction_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('drivers', 'driver_id')->onDelete('cascade');
            $table->integer('position'); // 1-10
            $table->timestamps();
            
            $table->unique(['race_prediction_id', 'position']);
            $table->unique(['race_prediction_id', 'driver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_positions');
    }
};

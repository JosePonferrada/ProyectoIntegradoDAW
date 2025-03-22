<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('races', function (Blueprint $table) {
      $table->id('race_id');
      $table->string('name', 255)->notNullable();
      $table->foreignId('season_id')->constrained('seasons', 'season_id');
      $table->foreignId('circuit_id')->constrained('circuits', 'circuit_id');
      $table->integer('round')->notNullable();
      $table->date('race_date')->notNullable();
      $table->time('start_time')->nullable();
      $table->decimal('distance', 6, 3)->nullable()->comment('en kilómetros');
      $table->integer('scheduled_laps')->nullable();
      $table->integer('completed_laps')->nullable();
      $table->time('race_duration')->nullable();
      $table->string('status', 50)->default('Scheduled')->comment('Scheduled, Completed, Canceled, Postponed');
      $table->string('weather_conditions', 255)->nullable();
      $table->decimal('avg_temperature', 5, 2)->nullable()->comment('en Celsius');
      $table->text('notes')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('races');
  }
};

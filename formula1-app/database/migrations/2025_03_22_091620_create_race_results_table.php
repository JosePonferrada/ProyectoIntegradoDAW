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
    Schema::create('race_results', function (Blueprint $table) {
      $table->id('result_id');
      $table->foreignId('race_id')->constrained('races', 'race_id');
      $table->foreignId('driver_id')->constrained('drivers', 'driver_id');
      $table->foreignId('constructor_id')->constrained('constructors', 'constructor_id');
      $table->integer('grid_position')->nullable();
      $table->integer('position')->nullable();
      $table->string('position_text', 10)->nullable()->comment('Para DNF, DSQ, etc.');
      $table->integer('position_order')->nullable();
      $table->decimal('points', 5, 2)->notNullable()->default(0);
      $table->integer('laps')->nullable();
      $table->time('race_time', 3)->nullable();
      $table->integer('fastest_lap')->nullable();
      $table->time('fastest_lap_time', 3)->nullable();
      $table->decimal('fastest_lap_speed', 6, 3)->nullable();
      $table->string('status', 50)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('race_results');
  }
};

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
    Schema::create('driver_standings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('season_id')->constrained('seasons', 'season_id');
      $table->foreignId('driver_id')->constrained('drivers', 'driver_id');
      $table->foreignId('race_id')->nullable()->constrained('races', 'race_id');
      $table->decimal('points', 6, 2)->notNullable()->default(0);
      $table->integer('position')->nullable();
      $table->integer('wins')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('driver_standings');
  }
};

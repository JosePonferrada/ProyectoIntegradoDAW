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
    Schema::create('driver_constructor_seasons', function (Blueprint $table) {
      $table->id();
      $table->foreignId('driver_id')->constrained('drivers', 'driver_id');
      $table->foreignId('constructor_id')->constrained('constructors', 'constructor_id');
      $table->foreignId('season_id')->constrained('seasons', 'season_id');
      $table->integer('position_number')->nullable();
      $table->timestamps();

      // Le asignamos un nombre personalizado a la clave foránea porque es muy larga
      $table->unique(['driver_id', 'constructor_id', 'season_id'])->name('driver_constructor_season_unique');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('driver_constructor_seasons', function (Blueprint $table) {
      $table->dropUnique('driver_constructor_season_unique');
    });
  }
};

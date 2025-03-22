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
    Schema::create('seasons', function (Blueprint $table) {
      $table->id('season_id');
      $table->year('year')->unique()->notNullable();
      $table->integer('races_count')->nullable();
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->unsignedBigInteger('reigning_champion_driver')->nullable();
      $table->unsignedBigInteger('reigning_champion_constructor')->nullable();
      $table->text('regulation_changes')->nullable();
      $table->timestamps();

      // Las claves foráneas se añadirán después de crear las tablas drivers y constructors
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('seasons');
  }
};

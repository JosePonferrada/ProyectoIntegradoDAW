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
    Schema::table('seasons', function (Blueprint $table) {
      $table->foreign('reigning_champion_driver')->references('driver_id')->on('drivers');
      $table->foreign('reigning_champion_constructor')->references('constructor_id')->on('constructors');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('seasons', function (Blueprint $table) {
      $table->dropForeign(['reigning_champion_driver']);
      $table->dropForeign(['reigning_champion_constructor']);
    });
  }
};

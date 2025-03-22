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
    Schema::create('qualifying_results', function (Blueprint $table) {
      $table->id('qualifying_id');
      $table->foreignId('race_id')->constrained('races', 'race_id');
      $table->foreignId('driver_id')->constrained('drivers', 'driver_id');
      $table->foreignId('constructor_id')->constrained('constructors', 'constructor_id');
      $table->integer('position')->nullable();
      $table->time('q1_time', 3)->nullable();
      $table->time('q2_time', 3)->nullable();
      $table->time('q3_time', 3)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('qualifying_results');
  }
};

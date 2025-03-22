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
    Schema::create('circuits', function (Blueprint $table) {
      $table->id('circuit_id');
      $table->string('name', 255)->notNullable();
      $table->string('location', 255)->notNullable();
      $table->foreignId('country_id')->nullable()->constrained('countries', 'country_id');
      $table->decimal('length', 6, 3)->notNullable()->comment('en kilómetros');
      $table->time('lap_record')->nullable();
      $table->string('lap_record_driver', 255)->nullable();
      $table->year('lap_record_year')->nullable();
      $table->string('map_image', 255)->nullable();
      $table->text('description')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('circuits');
  }
};

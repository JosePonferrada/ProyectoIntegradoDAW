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
    Schema::create('constructors', function (Blueprint $table) {
      $table->id('constructor_id');
      $table->string('name', 255)->notNullable();
      $table->foreignId('nationality')->nullable()->constrained('countries', 'country_id');
      $table->string('logo', 255)->nullable();
      $table->string('base', 255)->nullable();
      $table->year('first_team_entry')->nullable();
      $table->string('team_chief', 255)->nullable();
      $table->string('technical_chief', 255)->nullable();
      $table->string('chassis', 255)->nullable();
      $table->string('power_unit', 255)->nullable();
      $table->string('official_website', 255)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('constructors');
  }
};

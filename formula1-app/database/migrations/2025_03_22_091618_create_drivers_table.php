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
    Schema::create('drivers', function (Blueprint $table) {
      $table->id('driver_id');
      $table->string('first_name', 100)->notNullable();
      $table->string('last_name', 100)->notNullable();
      $table->char('code', 3)->nullable();
      $table->integer('number')->nullable();
      $table->date('date_of_birth')->nullable();
      $table->foreignId('nationality')->nullable()->constrained('countries', 'country_id');
      $table->boolean('active')->default(true);
      $table->text('biography')->nullable();
      $table->string('image', 255)->nullable();
      $table->year('debut_year')->nullable();
      $table->integer('championships')->default(0);
      $table->integer('wins')->default(0);
      $table->integer('podiums')->default(0);
      $table->integer('poles')->default(0);
      $table->integer('fastest_laps')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('drivers');
  }
};

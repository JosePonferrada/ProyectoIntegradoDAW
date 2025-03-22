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
    Schema::create('steward_decisions', function (Blueprint $table) {
      $table->id('decision_id');
      $table->foreignId('race_id')->nullable()->constrained('races', 'race_id');
      $table->string('document_path', 255)->notNullable();
      $table->timestamp('upload_date')->useCurrent();
      $table->text('description')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('steward_decisions');
  }
};

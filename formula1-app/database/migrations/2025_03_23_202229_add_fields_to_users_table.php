<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
  public function up()
  {
    // Comprobar si las tablas existen
    $driversExists = Schema::hasTable('drivers');
    $constructorsExists = Schema::hasTable('constructors');

    Schema::table('users', function (Blueprint $table) use ($driversExists, $constructorsExists) {
      // Verificar si las columnas no existen antes de añadirlas
      if (!Schema::hasColumn('users', 'role')) {
        $table->enum('role', ['user', 'admin'])->default('user')->after('password');
      }

      if (!Schema::hasColumn('users', 'avatar')) {
        $table->string('avatar')->nullable()->after('role');
      }

      if (!Schema::hasColumn('users', 'country_code')) {
        $table->string('country_code', 2)->nullable()->after('avatar');
      }

      if (!Schema::hasColumn('users', 'favorite_driver_id') && $driversExists) {
        $table->unsignedBigInteger('favorite_driver_id')->nullable()->after('country_code');
        $table->foreign('favorite_driver_id')->references('driver_id')->on('drivers')->onDelete('set null');
      }

      if (!Schema::hasColumn('users', 'favorite_constructor_id') && $constructorsExists) {
        $table->unsignedBigInteger('favorite_constructor_id')->nullable()->after('favorite_driver_id');
        $table->foreign('favorite_constructor_id')->references('constructor_id')->on('constructors')->onDelete('set null');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      // Eliminar claves foráneas primero
      if (Schema::hasColumn('users', 'favorite_driver_id')) {
        $table->dropForeign(['favorite_driver_id']);
      }

      if (Schema::hasColumn('users', 'favorite_constructor_id')) {
        $table->dropForeign(['favorite_constructor_id']);
      }

      // Luego eliminar columnas
      $columnsToRemove = [];
      foreach (['role', 'avatar', 'country_code', 'favorite_driver_id', 'favorite_constructor_id'] as $column) {
        if (Schema::hasColumn('users', $column)) {
          $columnsToRemove[] = $column;
        }
      }

      if (!empty($columnsToRemove)) {
        $table->dropColumn($columnsToRemove);
      }
    });
  }
}
;

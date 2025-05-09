<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Primero eliminamos cualquier clave foránea existente
        Schema::table('users', function (Blueprint $table) {
            // Eliminar la columna actual
            $table->dropColumn('country_id');
        });

        // Luego creamos la columna con el tipo correcto
        Schema::table('users', function (Blueprint $table) {
            // Crear la columna con el tipo correcto
            $table->unsignedBigInteger('country_id')->nullable()->after('avatar');
            
            // Añadir la relación
            $table->foreign('country_id')
                  ->references('country_id')
                  ->on('countries')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->string('country_id', 2)->nullable()->after('avatar');
        });
    }
};

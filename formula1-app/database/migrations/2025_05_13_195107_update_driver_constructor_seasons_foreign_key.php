<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_constructor_seasons', function (Blueprint $table) {
            // Eliminar la restricción foreign key existente
            $table->dropForeign(['driver_id']);
            
            // Crear una nueva con cascade delete
            $table->foreign('driver_id')
                  ->references('driver_id')
                  ->on('drivers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_constructor_seasons', function (Blueprint $table) {
            // Eliminar la restricción con cascade
            $table->dropForeign(['driver_id']);
            
            // Restaurar la restricción original sin cascade
            $table->foreign('driver_id')
                  ->references('driver_id')
                  ->on('drivers');
        });
    }
};

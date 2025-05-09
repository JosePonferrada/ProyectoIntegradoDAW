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
        Schema::table('circuits', function (Blueprint $table) {
            // Añadir nueva columna para layout_image después de map_image
            $table->string('layout_image')->nullable()->after('map_image');
            
            // Modificar la columna lap_record para soportar milisegundos
            // Primero hacemos nullable la columna para poder modificarla
            $table->time('lap_record', 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('circuits', function (Blueprint $table) {
            // Eliminar la columna layout_image
            $table->dropColumn('layout_image');
            
            // Restaurar lap_record a su estado original (sin precisión de milisegundos)
            $table->time('lap_record')->nullable()->change();
        });
    }
};

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
        Schema::table('races', function (Blueprint $table) {
            // 1. Añadir columnas para las fechas de todas las sesiones
            $table->date('practice1_date')->nullable()->after('race_date');
            $table->date('practice2_date')->nullable()->after('practice1_date');
            $table->date('practice3_date')->nullable()->after('practice2_date');
            $table->date('qualifying_date')->nullable()->after('practice3_date');
            $table->date('sprint_date')->nullable()->after('qualifying_date');
            $table->date('sprint_qualifying_date')->nullable()->after('sprint_date');
            
            // 2. Añadir columnas para las horas separadas
            $table->time('practice1_time')->nullable()->after('sprint_qualifying_date');
            $table->time('practice2_time')->nullable()->after('practice1_time');
            $table->time('practice3_time')->nullable()->after('practice2_time');
            $table->time('qualifying_time')->nullable()->after('practice3_time');
            $table->time('sprint_time')->nullable()->after('qualifying_time');
            $table->time('sprint_qualifying_time')->nullable()->after('sprint_time');
            
            // 3. Añadir columna para el formato del fin de semana con más opciones
            $table->enum('weekend_format', [
                'traditional',   // Formato tradicional: P1, P2, P3, Q, Race
                'sprint',        // Formato sprint: P1, SQ, Sprint, Q, Race
            ])->default('traditional')->after('sprint_qualifying_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            // Eliminar todas las columnas añadidas
            $table->dropColumn([
                'practice1_date',
                'practice2_date',
                'practice3_date',
                'qualifying_date',
                'sprint_date',
                'sprint_qualifying_date',
                'practice1_time',
                'practice2_time',
                'practice3_time',
                'qualifying_time',
                'sprint_time',
                'sprint_qualifying_time',
                'weekend_format'
            ]);
        });
    }
};

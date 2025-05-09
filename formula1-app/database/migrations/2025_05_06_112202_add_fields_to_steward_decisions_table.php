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
        Schema::table('steward_decisions', function (Blueprint $table) {
            // Nuevos campos basados en el documento oficial
            $table->string('document_number')->nullable()->after('decision_id');
            $table->foreignId('driver_id')->nullable()->after('race_id')->constrained('drivers', 'driver_id');
            $table->foreignId('team_id')->nullable()->after('driver_id')->constrained('constructors', 'constructor_id');
            $table->string('session_type')->nullable()->after('team_id');
            $table->string('incident_time')->nullable()->after('session_type');
            $table->string('infringement_article')->nullable()->after('description');
            $table->string('decision_type')->nullable()->after('infringement_article');
            $table->string('penalty_detail')->nullable()->after('decision_type');
            $table->text('content')->nullable()->after('penalty_detail');
            $table->json('stewards')->nullable()->after('content');
            
            // Renombrar campo para mayor claridad
            $table->renameColumn('description', 'fact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('steward_decisions', function (Blueprint $table) {
            $table->renameColumn('fact', 'description');
            
            $table->dropColumn([
                'document_number',
                'driver_id',
                'team_id',
                'session_type',
                'incident_time',
                'infringement_article',
                'decision_type',
                'penalty_detail',
                'content',
                'stewards'
            ]);
        });
    }
};

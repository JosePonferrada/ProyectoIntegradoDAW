<?php

namespace App\Observers;

use App\Models\RaceResult;
use App\Models\DriverStanding;
use App\Models\ConstructorStanding;
use App\Models\Race;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RaceResultObserver
{
    /**
     * Handle the RaceResult "created" event.
     */
    public function created(RaceResult $raceResult): void
    {
        $this->updateStandings($raceResult);
    }

    /**
     * Handle the RaceResult "updated" event.
     */
    public function updated(RaceResult $raceResult): void
    {
        $this->updateStandings($raceResult);
    }

    /**
     * Handle the RaceResult "deleted" event.
     */
    public function deleted(RaceResult $raceResult): void
    {
        // Si se borra un resultado, recalcular las clasificaciones
        $this->recalculateAllStandings($raceResult->race_id);
    }

    /**
     * Actualiza las clasificaciones de pilotos y constructores
     */
    private function updateStandings(RaceResult $raceResult): void
    {
        try {
            // Obtener datos necesarios
            $race = Race::where('race_id', $raceResult->race_id)->first();
            
            if (!$race) {
                Log::warning("No se encontró la carrera ID {$raceResult->race_id}");
                return;
            }
            
            $seasonId = $race->season_id;
            $driverId = $raceResult->driver_id;

            // Encontrar el constructor del piloto para esta carrera
            $constructorId = $raceResult->constructor_id;

            if (!$constructorId) {
                Log::warning("No se encontró constructor para el resultado ID {$raceResult->id}");
                return;
            }

            // ACTUALIZAR CLASIFICACIÓN DE PILOTO
            $this->updateDriverStanding($raceResult, $race, $driverId);

            // ACTUALIZAR CLASIFICACIÓN DE CONSTRUCTOR
            $this->updateConstructorStanding($raceResult, $race, $constructorId);
            
        } catch (\Exception $e) {
            Log::error("Error actualizando clasificaciones: " . $e->getMessage());
        }
    }

    /**
     * Actualiza la clasificación de un piloto específico
     */
    private function updateDriverStanding(RaceResult $raceResult, Race $race, int $driverId): void
    {
        // Buscar o crear standing para este piloto en esta carrera
        $driverStanding = DriverStanding::firstOrNew([
            'driver_id' => $driverId,
            'race_id' => $race->race_id
        ]);

        // Si es nuevo, asignar temporada
        if (!$driverStanding->exists) {
            $driverStanding->season_id = $race->season_id;
        }

        // Calcular puntos acumulados y victorias para este piloto en la temporada hasta esta carrera
        $raceIds = Race::where('season_id', $race->season_id)
            ->where('race_date', '<=', $race->race_date)
            ->pluck('race_id');
            
        $totalPoints = RaceResult::where('driver_id', $driverId)
            ->whereIn('race_id', $raceIds)
            ->sum('points');

        $victories = RaceResult::where('driver_id', $driverId)
            ->whereIn('race_id', $raceIds)
            ->where('position', 1)
            ->count();

        // Actualizar driver standing
        $driverStanding->points = $totalPoints;
        $driverStanding->wins = $victories;
        $driverStanding->save();

        // Recalcular posiciones para todos los pilotos en esta carrera
        $this->recalculateDriverPositions($race->race_id);
    }

    /**
     * Actualiza la clasificación de un constructor específico
     */
    private function updateConstructorStanding(RaceResult $raceResult, Race $race, int $constructorId): void
    {
        // Buscar o crear standing para este constructor en esta carrera
        $constructorStanding = ConstructorStanding::firstOrNew([
            'constructor_id' => $constructorId,
            'race_id' => $race->race_id
        ]);

        // Si es nuevo, asignar temporada
        if (!$constructorStanding->exists) {
            $constructorStanding->season_id = $race->season_id;
        }

        // Calcular puntos acumulados y victorias para este constructor
        $raceIds = Race::where('season_id', $race->season_id)
            ->where('race_date', '<=', $race->race_date)
            ->pluck('race_id');
            
        $totalPoints = RaceResult::where('constructor_id', $constructorId)
            ->whereIn('race_id', $raceIds)
            ->sum('points');

        $victories = RaceResult::where('constructor_id', $constructorId)
            ->whereIn('race_id', $raceIds)
            ->where('position', 1)
            ->count();

        // Actualizar constructor standing
        $constructorStanding->points = $totalPoints;
        $constructorStanding->wins = $victories;
        $constructorStanding->save();

        // Recalcular posiciones para todos los constructores en esta carrera
        $this->recalculateConstructorPositions($race->race_id);
    }

    /**
     * Recalcula las posiciones de todos los pilotos en una carrera
     */
    private function recalculateDriverPositions(int $raceId): void
    {
        $standings = DriverStanding::where('race_id', $raceId)
            ->orderByDesc('points')
            ->get();

        $position = 1;
        foreach ($standings as $standing) {
            $standing->position = $position++;
            $standing->save();
        }
    }

    /**
     * Recalcula las posiciones de todos los constructores en una carrera
     */
    private function recalculateConstructorPositions(int $raceId): void
    {
        $standings = ConstructorStanding::where('race_id', $raceId)
            ->orderByDesc('points')
            ->get();

        $position = 1;
        foreach ($standings as $standing) {
            $standing->position = $position++;
            $standing->save();
        }
    }

    /**
     * Recalcula todas las clasificaciones tras borrar un resultado
     */
    private function recalculateAllStandings(int $raceId): void
    {
        $race = Race::findOrFail($raceId);
        
        // Obtener todos los pilotos y constructores que participaron
        $results = RaceResult::where('race_id', $raceId)->get();
        
        foreach ($results as $result) {
            $this->updateStandings($result);
        }
    }
}

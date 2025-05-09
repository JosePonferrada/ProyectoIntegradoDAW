<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverStandingResource;
use App\Models\DriverStanding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DriverStandingController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    if ($request->has('season_id') && $request->has('accumulated') && $request->accumulated === 'true') {
        // CLASIFICACIÓN ACUMULADA: Devolver la clasificación actualizada con puntos acumulados
        return $this->getAccumulatedStandings($request->season_id);
    } 
    else if ($request->has('race_id')) {
        // CARRERA ESPECÍFICA: Filtrar por carrera específica
        $standings = DriverStanding::where('race_id', $request->race_id)
            ->with(['driver', 'race', 'season', 'driver.nationality'])
            ->orderBy('position')
            ->get();
    }
    else if ($request->has('season_id')) {
        // ÚLTIMA CARRERA DE LA TEMPORADA: Comportamiento actual
        $seasonId = $request->season_id;
        $lastRaceId = \DB::table('driver_standings')
            ->join('races', 'driver_standings.race_id', '=', 'races.race_id')
            ->where('driver_standings.season_id', $seasonId)
            ->orderBy('races.race_date', 'desc')
            ->value('driver_standings.race_id');
            
        $standings = DriverStanding::where('race_id', $lastRaceId)
            ->with(['driver', 'race', 'season', 'driver.nationality'])
            ->orderBy('position')
            ->get();
    }
    
    // Obtener información de constructores para estos pilotos en la temporada actual
    $driverIds = $standings->pluck('driver_id')->toArray();
    $season = $request->has('season_id') ? $request->season_id : 
             ($standings->first() ? $standings->first()->season_id : null);
    
    if ($season) {
      $seasonYear = \App\Models\Season::find($season)->year;
      
      $driverConstructors = \DB::table('driver_constructor_seasons')
        ->join('seasons', 'driver_constructor_seasons.season_id', '=', 'seasons.season_id')
        ->join('constructors', 'driver_constructor_seasons.constructor_id', '=', 'constructors.constructor_id')
        ->where('seasons.year', $seasonYear)
        ->whereIn('driver_constructor_seasons.driver_id', $driverIds)
        ->select(
          'driver_constructor_seasons.driver_id',
          'driver_constructor_seasons.constructor_id',
          'driver_constructor_seasons.position_number',
          'constructors.name as constructor_name',
          'constructors.logo as constructor_logo'
        )
        ->get()
        ->keyBy('driver_id');
      
      // Añadir constructor a cada standing
      foreach ($standings as $standing) {
        $driverInfo = $driverConstructors[$standing->driver_id] ?? null;
        if ($driverInfo) {
          $standing->constructor_id = $driverInfo->constructor_id;
          $standing->constructor_name = $driverInfo->constructor_name;
          $standing->constructor_logo = $driverInfo->constructor_logo;
          $standing->position_number = $driverInfo->position_number;
        }
      }
    }

    return DriverStandingResource::collection($standings);
  }

  /**
   * Obtener clasificación acumulada para una temporada
   */
  private function getAccumulatedStandings($seasonId)
  {
    // 1. Obtener todas las carreras de la temporada en orden cronológico
    $races = \App\Models\Race::where('season_id', $seasonId)
        ->orderBy('race_date', 'asc')
        ->get();
    
    // 2. Calcular puntos acumulados para cada piloto
    $driverTotals = [];
    $driverWins = [];
    
    foreach ($races as $race) {
        $raceResults = \App\Models\RaceResult::where('race_id', $race->race_id)
            ->get();
            
        foreach ($raceResults as $result) {
            if (!isset($driverTotals[$result->driver_id])) {
                $driverTotals[$result->driver_id] = 0;
                $driverWins[$result->driver_id] = 0;
            }
            
            $driverTotals[$result->driver_id] += $result->points;
            
            if ($result->position == 1) {
                $driverWins[$result->driver_id]++;
            }
        }
    }
    
    // 3. Ordenar pilotos por puntos y crear clasificación
    arsort($driverTotals);
    
    // 4. Crear clasificación virtual con datos acumulados
    $standings = [];
    $position = 1;
    $lastRace = $races->last();
    
    foreach ($driverTotals as $driverId => $points) {
        $driver = \App\Models\Driver::find($driverId);
        
        // Obtener constructor del piloto en esta temporada
        $constructor = \DB::table('driver_constructor_seasons')
            ->join('constructors', 'driver_constructor_seasons.constructor_id', '=', 'constructors.constructor_id')
            ->where('driver_constructor_seasons.driver_id', $driverId)
            ->where('driver_constructor_seasons.season_id', $seasonId)
            ->select(
                'constructors.constructor_id',
                'constructors.name as constructor_name',
                'constructors.logo as constructor_logo',
                'driver_constructor_seasons.position_number'
            )
            ->first();
        
        $standing = new \App\Models\DriverStanding();
        $standing->id = "accumulated-$driverId";
        $standing->driver_id = $driverId;
        $standing->season_id = $seasonId;
        $standing->race_id = $lastRace ? $lastRace->race_id : null;
        $standing->points = $points;
        $standing->position = $position++;
        $standing->wins = $driverWins[$driverId] ?? 0;
        
        // Añadir relaciones
        $standing->setRelation('driver', $driver);
        $standing->setRelation('season', \App\Models\Season::find($seasonId));
        if ($lastRace) {
            $standing->setRelation('race', $lastRace);
        }
        
        // Añadir datos del constructor
        if ($constructor) {
            $standing->constructor_id = $constructor->constructor_id;
            $standing->constructor_name = $constructor->constructor_name;
            $standing->constructor_logo = $constructor->constructor_logo;
            $standing->position_number = $constructor->position_number;
        }
        
        $standings[] = $standing;
    }
    
    return DriverStandingResource::collection(collect($standings));
  }

  public function store(Request $request): DriverStandingResource
  {
    $validated = $request->validate([
      'season_id' => 'required|exists:seasons,season_id',
      'driver_id' => 'required|exists:drivers,driver_id',
      'race_id' => 'nullable|exists:races,race_id',
      'points' => 'required|numeric',
      'position' => 'nullable|integer',
      'wins' => 'nullable|integer|min:0',
    ]);

    $driverStanding = DriverStanding::create($validated);

    return new DriverStandingResource($driverStanding->load(['season', 'driver', 'race']));
  }

  public function show(DriverStanding $driverStanding): DriverStandingResource
  {
    return new DriverStandingResource($driverStanding->load(['season', 'driver', 'race']));
  }

  public function update(Request $request, DriverStanding $driverStanding): DriverStandingResource
  {
    $validated = $request->validate([
      'season_id' => 'sometimes|required|exists:seasons,season_id',
      'driver_id' => 'sometimes|required|exists:drivers,driver_id',
      'race_id' => 'nullable|exists:races,race_id',
      'points' => 'sometimes|required|numeric',
      'position' => 'nullable|integer',
      'wins' => 'nullable|integer|min:0',
    ]);

    $driverStanding->update($validated);

    return new DriverStandingResource($driverStanding->load(['season', 'driver', 'race']));
  }

  public function destroy(DriverStanding $driverStanding): Response
  {
    $driverStanding->delete();

    return response()->noContent();
  }

  /**
   * Obtener progresión de pilotos a lo largo de la temporada (para gráficos)
   */
  public function getDriverProgressionBySeason(Request $request, $seasonId)
  {
    try {
      $season = \App\Models\Season::findOrFail($seasonId);
      $currentYear = $season->year;
      
      // Obtener todas las carreras de la temporada en orden cronológico
      $races = \App\Models\Race::where('season_id', $seasonId)
          ->orderBy('race_date', 'asc')
          ->get();
      
      // Primero, obtener la información de driver_constructor_seasons para este año
      $driverConstructorsMap = \DB::table('driver_constructor_seasons')
          ->join('seasons', 'driver_constructor_seasons.season_id', '=', 'seasons.season_id')
          ->join('constructors', 'driver_constructor_seasons.constructor_id', '=', 'constructors.constructor_id')
          ->where('seasons.year', $currentYear)
          ->select(
              'driver_constructor_seasons.driver_id',
              'driver_constructor_seasons.constructor_id',
              'driver_constructor_seasons.position_number',
              'constructors.name as constructor_name',
              'constructors.logo as constructor_logo'
          )
          ->get()
          ->keyBy('driver_id');
      
      $raceData = $races->map(function($race) use ($driverConstructorsMap) {
          // Obtener clasificación después de esta carrera
          $standings = DriverStanding::where('race_id', $race->race_id)
              ->with(['driver'])
              ->orderBy('position', 'asc')
              ->get();
          
          return [
              'id' => $race->race_id,
              'name' => $race->name,
              'date' => $race->race_date,
              'standings' => $standings->map(function($standing) use ($driverConstructorsMap) {
                  // Obtener información del constructor directamente del mapa
                  $driverInfo = $driverConstructorsMap[$standing->driver_id] ?? null;
                  
                  return [
                      'driver_id' => $standing->driver_id,
                      'constructor_id' => $driverInfo ? $driverInfo->constructor_id : null,
                      'constructor_name' => $driverInfo ? $driverInfo->constructor_name : null,
                      'constructor_logo' => $driverInfo ? $driverInfo->constructor_logo : null,
                      'position_number' => $driverInfo ? $driverInfo->position_number : null, // Posición en el equipo
                      'points' => $standing->points,
                      'position' => $standing->position, // Posición en la clasificación
                      'driver_code' => $standing->driver->code ?? null,
                      'driver_name' => $standing->driver->full_name ?? null,
                  ];
              })
          ];
      });
      
      return response()->json([
          'season_id' => $season->season_id,
          'year' => $season->year,
          'races' => $raceData
      ]);
      } catch (\Exception $e) {
          \Log::error('Error getting driver progression: ' . $e->getMessage());
          return response()->json([
              'message' => 'Error getting driver progression',
              'error' => $e->getMessage()
          ], 500);
      }
  }

  /**
   * Actualizar clasificación general después de una carrera
   */
  public function updateStandingsAfterRace($raceId)
  {
    try {
        $race = \App\Models\Race::findOrFail($raceId);
        $seasonId = $race->season_id;
        
        // Obtener todos los pilotos que participaron en carreras de esta temporada
        $driverIds = \App\Models\RaceResult::join('races', 'race_results.race_id', '=', 'races.race_id')
            ->where('races.season_id', $seasonId)
            ->pluck('driver_id')
            ->unique();
        
        foreach ($driverIds as $driverId) {
            // Calcular puntos acumulados hasta esta carrera
            $totalPoints = \App\Models\RaceResult::join('races', 'race_results.race_id', '=', 'races.race_id')
                ->where('races.season_id', $seasonId)
                ->where('race_results.driver_id', $driverId)
                ->where('races.race_date', '<=', $race->race_date)
                ->sum('race_results.points');
            
            // Contar victorias hasta esta carrera
            $wins = \App\Models\RaceResult::join('races', 'race_results.race_id', '=', 'races.race_id')
                ->where('races.season_id', $seasonId)
                ->where('race_results.driver_id', $driverId)
                ->where('races.race_date', '<=', $race->race_date)
                ->where('race_results.position', 1)
                ->count();
            
            // Crear o actualizar en driver_standings
            \App\Models\DriverStanding::updateOrCreate(
                ['race_id' => $raceId, 'driver_id' => $driverId],
                [
                    'season_id' => $seasonId,
                    'points' => $totalPoints,
                    'wins' => $wins
                ]
            );
        }
        
        // Actualizar posiciones basadas en puntos
        $standings = \App\Models\DriverStanding::where('race_id', $raceId)
            ->orderByDesc('points')
            ->get();
            
        $position = 1;
        foreach ($standings as $standing) {
            $standing->position = $position++;
            $standing->save();
        }
        
        return "Clasificación actualizada correctamente";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
  }
}

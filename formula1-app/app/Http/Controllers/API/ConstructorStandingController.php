<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstructorStandingResource;
use App\Models\ConstructorStanding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Constructor;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

class ConstructorStandingController extends Controller
{
  /**
   * Get accumulated standings for a season
   */
  private function getAccumulatedStandings($seasonId)
  {
      try {
          // 1. Get all races from the season in chronological order
          $races = \App\Models\Race::where('season_id', $seasonId)
              ->orderBy('race_date', 'asc')
              ->get();
          
          if ($races->isEmpty()) {
              return \App\Http\Resources\ConstructorStandingResource::collection(collect([]));
          }
          
          // 2. Calculate accumulated points for each constructor
          $constructorTotals = [];
          $constructorWins = [];
          
          foreach ($races as $race) {
              // Get race results
              $raceResults = \App\Models\RaceResult::with(['driver'])
                  ->where('race_id', $race->race_id)
                  ->get();
                  
              foreach ($raceResults as $result) {
                  // Find the constructor for this driver in this season
                  $driverConstructor = \DB::table('driver_constructor_seasons')
                      ->where('driver_id', $result->driver_id)
                      ->where('season_id', $seasonId)
                      ->first();
                  
                  if (!$driverConstructor || !$driverConstructor->constructor_id) continue;
                  
                  $constructorId = $driverConstructor->constructor_id;
                  
                  if (!isset($constructorTotals[$constructorId])) {
                      $constructorTotals[$constructorId] = 0;
                      $constructorWins[$constructorId] = 0;
                  }
                  
                  // Add points to constructor
                  $constructorTotals[$constructorId] += (float)$result->points;
                  
                  // Count win if driver finished first
                  if ($result->position == 1) {
                      $constructorWins[$constructorId]++;
                  }
              }
          }
          
          // No data, return empty array
          if (empty($constructorTotals)) {
              return \App\Http\Resources\ConstructorStandingResource::collection(collect([]));
          }
          
          // 3. Sort constructors by points
          arsort($constructorTotals);
          
          // 4. Create virtual standings with accumulated data
          $standings = [];
          $position = 1;
          $lastRace = $races->last();
          
          foreach ($constructorTotals as $constructorId => $points) {
              $constructor = \App\Models\Constructor::with('nationality')->find($constructorId);
              
              if (!$constructor) continue;
              
              // Create standing object
              $standing = new \App\Models\ConstructorStanding();
              $standing->id = "accumulated-{$constructorId}";
              $standing->constructor_id = $constructorId;
              $standing->season_id = $seasonId;
              $standing->race_id = $lastRace ? $lastRace->race_id : null;
              $standing->points = $points;
              $standing->position = $position++;
              $standing->wins = $constructorWins[$constructorId] ?? 0;
              
              // Add relationships
              $standing->setRelation('constructor', $constructor);
              $standing->setRelation('season', \App\Models\Season::find($seasonId));
              if ($lastRace) {
                  $standing->setRelation('race', $lastRace);
              }

              // Log constructor nationality information
              \Log::info("Constructor {$constructor->name} nationality:", [
                  'has_relation' => $constructor->relationLoaded('nationality'),
                  'nationality_id' => $constructor->nationality_id ?? 'null',
                  'nationality_obj' => $constructor->nationality ? json_encode($constructor->nationality) : 'null'
              ]);
              
              $standings[] = $standing;
          }
          
          return \App\Http\Resources\ConstructorStandingResource::collection(collect($standings));
      } catch (\Exception $e) {
          // Log error for debugging
          \Log::error('Error in getAccumulatedStandings: ' . $e->getMessage());
          \Log::error($e->getTraceAsString());
          
          // Return empty collection in case of error
          return \App\Http\Resources\ConstructorStandingResource::collection(collect([]));
      }
  }

  /**
   * Mostrar listado de clasificaciones
   */
  public function index(Request $request)
  {
      // Añadir log para depuración
      \Log::info('ConstructorStandings request params: ' . json_encode($request->all()));
      
      // Si se solicita clasificación acumulada
      if ($request->has('season_id') && $request->has('accumulated') && $request->accumulated === 'true') {
          return $this->getAccumulatedStandings($request->season_id);
      } 
      
      $query = \App\Models\ConstructorStanding::query()
          ->with(['constructor', 'constructor.nationality', 'race', 'season']);
      
      // Si se solicita por carrera específica
      if ($request->has('race_id')) {
          $raceId = $request->race_id;
          \Log::info("Filtrando por race_id: {$raceId}");
          $query->where('race_id', $raceId);
      } 
      // Si se solicita por temporada (sin acumulado)
      else if ($request->has('season_id')) {
          $seasonId = $request->season_id;
          $query->where('season_id', $seasonId);
          
          // Obtener la última carrera
          $lastRace = \App\Models\Race::where('season_id', $seasonId)
              ->orderByDesc('race_date')
              ->first();
          
          if ($lastRace) {
              $query->where('race_id', $lastRace->race_id);
          }
      }
      
      $standings = $query->orderBy('position')->get();
      
      return \App\Http\Resources\ConstructorStandingResource::collection($standings);
  }

  public function store(Request $request): ConstructorStandingResource
  {
    $validated = $request->validate([
      'season_id' => 'required|exists:seasons,season_id',
      'constructor_id' => 'required|exists:constructors,constructor_id',
      'race_id' => 'nullable|exists:races,race_id',
      'points' => 'required|numeric',
      'position' => 'nullable|integer',
      'wins' => 'nullable|integer|min:0',
    ]);

    $constructorStanding = ConstructorStanding::create($validated);

    return new ConstructorStandingResource($constructorStanding->load(['season', 'constructor', 'race']));
  }

  public function show(ConstructorStanding $constructorStanding): ConstructorStandingResource
  {
    return new ConstructorStandingResource($constructorStanding->load(['season', 'constructor', 'race']));
  }

  public function update(Request $request, ConstructorStanding $constructorStanding): ConstructorStandingResource
  {
    $validated = $request->validate([
      'season_id' => 'sometimes|required|exists:seasons,season_id',
      'constructor_id' => 'sometimes|required|exists:constructors,constructor_id',
      'race_id' => 'nullable|exists:races,race_id',
      'points' => 'sometimes|required|numeric',
      'position' => 'nullable|integer',
      'wins' => 'nullable|integer|min:0',
    ]);

    $constructorStanding->update($validated);

    return new ConstructorStandingResource($constructorStanding->load(['season', 'constructor', 'race']));
  }

  public function destroy(ConstructorStanding $constructorStanding): Response
  {
    $constructorStanding->delete();

    return response()->noContent();
  }

  /**
   * Obtener progresión de constructores a lo largo de la temporada (para gráficos)
   */
  public function getConstructorProgressionBySeason(Request $request, $seasonId)
  {
    try {
        $season = Season::findOrFail($seasonId);

        $races = Race::where('season_id', $seasonId)
            ->orderBy('race_date', 'asc')
            ->get();

        if ($races->isEmpty()) {
            return response()->json([
                'season_id' => $season->season_id,
                'year' => $season->year,
                'races' => []
            ]);
        }

        // Obtener todos los constructores que participan en esta temporada
        $participatingConstructorIds = DB::table('driver_constructor_seasons')
            ->where('season_id', $seasonId)
            ->distinct()
            ->pluck('constructor_id');

        $allSeasonConstructors = Constructor::whereIn('constructor_id', $participatingConstructorIds)
            ->with('nationality') // Cargar nacionalidad si es necesaria
            ->get()
            ->keyBy('constructor_id');

        // Mapear qué pilotos pertenecen a qué constructor para esta temporada
        $driverToConstructorMap = DB::table('driver_constructor_seasons')
            ->where('season_id', $seasonId)
            ->select('driver_id', 'constructor_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->driver_id => $item->constructor_id];
            });

        // Inicializar datos acumulados para todos los constructores de la temporada
        $constructorAccumulatedData = [];
        foreach ($allSeasonConstructors as $constructorId => $constructorModel) {
            $constructorAccumulatedData[$constructorId] = [
                'constructor_id' => $constructorId,
                'points' => 0,
                'wins' => 0, // Victorias del equipo en carreras
                'constructor_model' => $constructorModel,
            ];
        }

        $raceProgressionOutput = [];

        foreach ($races as $race) {
            $currentRaceResults = RaceResult::where('race_id', $race->race_id)->get();
            $constructorsWithWinThisRace = []; // Para contar solo una victoria por equipo por carrera

            foreach ($currentRaceResults as $result) {
                $driverId = $result->driver_id;
                $constructorIdForDriver = $driverToConstructorMap[$driverId] ?? null;

                if ($constructorIdForDriver && isset($constructorAccumulatedData[$constructorIdForDriver])) {
                    $constructorAccumulatedData[$constructorIdForDriver]['points'] += $result->points;

                    if ($result->position == 1 && !isset($constructorsWithWinThisRace[$constructorIdForDriver])) {
                        $constructorAccumulatedData[$constructorIdForDriver]['wins']++;
                        $constructorsWithWinThisRace[$constructorIdForDriver] = true;
                    }
                }
            }

            $currentRaceConstructorStandings = [];
            foreach ($constructorAccumulatedData as $constructorId => $data) {
                $currentRaceConstructorStandings[] = [
                    'constructor_id' => $constructorId,
                    'constructor_name' => $data['constructor_model']->name ?? 'Unknown Constructor',
                    'constructor_logo' => $data['constructor_model']->logo ?? null,
                    'nationality_name' => $data['constructor_model']->nationality->name ?? null, // Ejemplo
                    'nationality_code' => $data['constructor_model']->nationality->code ?? null, // Ejemplo
                    'points' => (float) $data['points'],
                    'wins' => (int) $data['wins'],
                ];
            }

            usort($currentRaceConstructorStandings, function ($a, $b) {
                if ($b['points'] != $a['points']) {
                    return $b['points'] <=> $a['points'];
                }
                if ($b['wins'] != $a['wins']) {
                    return $b['wins'] <=> $a['wins'];
                }
                return $a['constructor_id'] <=> $b['constructor_id'];
            });

            foreach ($currentRaceConstructorStandings as $idx => &$standingEntry) {
                $standingEntry['position'] = $idx + 1;
            }
            unset($standingEntry);

            $raceProgressionOutput[] = [
                'id' => $race->race_id,
                'name' => $race->name,
                'date' => $race->race_date,
                'standings' => $currentRaceConstructorStandings,
            ];
        }
        
        return response()->json([
            'season_id' => $season->season_id,
            'year' => $season->year,
            'races' => $raceProgressionOutput
        ]);

    } catch (\Exception $e) {
        \Log::error('Error getting constructor progression: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        \Log::error($e->getTraceAsString());
        return response()->json([
            'message' => 'Error getting constructor progression',
            'error' => $e->getMessage()
        ], 500);
    }
  }
}

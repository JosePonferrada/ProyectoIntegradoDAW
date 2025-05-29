<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RaceResource;
use App\Models\ConstructorStanding;
use App\Models\DriverStanding;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\QualifyingResult;
use App\Models\RacePrediction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse; // <-- AÑADIDO ESTO

class RaceController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    $query = Race::with(['circuit.country', 'season']);

    // Aplicar filtro por season_id si está presente en la solicitud
    if ($request->has('season_id') && $request->input('season_id') !== null) {
        $query->where('season_id', $request->input('season_id'));
    }

    // Ordenar por fecha de carrera (ascendente es más común para listados generales)
    // Si necesitas descendente para 'latestRaces' específicamente, ese filtrado
    // y ordenación ya lo haces en el frontend.
    // Para el conteo total de la temporada, el orden no importa tanto como el filtro.
    $query->orderBy('race_date', 'asc'); 

    return RaceResource::collection($query->get());

  }

  public function store(Request $request): RaceResource
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'season_id' => 'required|exists:seasons,season_id',
      'circuit_id' => 'required|exists:circuits,circuit_id',
      'round' => 'required|integer|min:1',
      'race_date' => 'required|date',
      'start_time' => 'nullable',
      'distance' => 'nullable|numeric',
      'scheduled_laps' => 'nullable|integer|min:1',
      'completed_laps' => 'nullable|integer|min:0',
      'race_duration' => 'nullable',
      'status' => 'nullable|string|max:50',
      'weather_conditions' => 'nullable|string|max:255',
      'avg_temperature' => 'nullable|numeric',
      'notes' => 'nullable|string',
      // Campos de la migración
      'practice1_date' => 'nullable|date',
      'practice1_time' => 'nullable',
      'practice2_date' => 'nullable|date',
      'practice2_time' => 'nullable',
      'practice3_date' => 'nullable|date',
      'practice3_time' => 'nullable',
      'qualifying_date' => 'nullable|date',
      'qualifying_time' => 'nullable',
      'sprint_qualifying_date' => 'nullable|date',
      'sprint_qualifying_time' => 'nullable',
      'sprint_date' => 'nullable|date',
      'sprint_time' => 'nullable',
      'weekend_format' => 'nullable|string|in:traditional,sprint'
    ]);

    $race = Race::create($validated);

    return new RaceResource($race->load(['circuit.country', 'season']));
  }

  public function show(Race $race): RaceResource
  {
    return new RaceResource($race->load([
      'circuit.country',
      'season',
      'results.driver',
      'results.constructor',
      'qualifyingResults.driver',
      'qualifyingResults.constructor'
    ]));
  }

  public function update(Request $request, Race $race): RaceResource
  {
    $validated = $request->validate([
      'name' => 'sometimes|required|string|max:255',
      'season_id' => 'sometimes|required|exists:seasons,season_id',
      'circuit_id' => 'sometimes|required|exists:circuits,circuit_id',
      'round' => 'sometimes|required|integer|min:1',
      'race_date' => 'sometimes|required|date',
      'start_time' => 'nullable',
      'distance' => 'nullable|numeric',
      'scheduled_laps' => 'nullable|integer|min:1',
      'completed_laps' => 'nullable|integer|min:0',
      'race_duration' => 'nullable',
      'status' => 'nullable|string|max:50',
      'weather_conditions' => 'nullable|string|max:255',
      'avg_temperature' => 'nullable|numeric',
      'notes' => 'nullable|string',
      // Campos de la migración
      'practice1_date' => 'nullable|date',
      'practice1_time' => 'nullable',
      'practice2_date' => 'nullable|date',
      'practice2_time' => 'nullable',
      'practice3_date' => 'nullable|date',
      'practice3_time' => 'nullable',
      'qualifying_date' => 'nullable|date',
      'qualifying_time' => 'nullable',
      'sprint_qualifying_date' => 'nullable|date',
      'sprint_qualifying_time' => 'nullable',
      'sprint_date' => 'nullable|date',
      'sprint_time' => 'nullable',
      'weekend_format' => 'nullable|string|in:traditional,sprint'
    ]);

    $race->update($validated);

    return new RaceResource($race->load(['circuit.country', 'season']));
  }

  public function destroy(Race $race): Response|JsonResponse // <-- MODIFICADO AQUÍ
  {
    DB::beginTransaction();
    try {
        // 1. Eliminar entidades dependientes
        RaceResult::where('race_id', $race->race_id)->delete();
        QualifyingResult::where('race_id', $race->race_id)->delete();
        RacePrediction::where('race_id', $race->race_id)->delete();
        ConstructorStanding::where('race_id', $race->race_id)->delete();
        DriverStanding::where('race_id', $race->race_id)->delete();

        // 2. Ahora elimina la carrera
        $race->delete();

        DB::commit();
        
        Log::info("Race ID: {$race->race_id} and all associated data deleted successfully.");
        // response()->noContent() devuelve Illuminate\Http\Response, lo cual está bien
        return response()->noContent(); 

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error deleting race ID {$race->race_id}: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
        // response()->json() devuelve Illuminate\Http\JsonResponse, que ahora es aceptado por el union type
        return response()->json(['message' => 'Error deleting race.', 'error' => $e->getMessage()], 500);
    }
  }

  public function getRacesBySeason($seasonId)
  {
    $races = \App\Models\Race::where('season_id', $seasonId)
        ->orderBy('race_date', 'asc')
        ->get();
    
    return response()->json([
        'data' => $races
    ]);
  }
}

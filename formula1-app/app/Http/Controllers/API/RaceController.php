<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RaceResource;
use App\Models\Race;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RaceController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return RaceResource::collection(
      Race::with(['circuit.country', 'season'])->orderBy('race_date', 'desc')->get()
    );
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
    ]);

    $race->update($validated);

    return new RaceResource($race->load(['circuit.country', 'season']));
  }

  public function destroy(Race $race): Response
  {
    $race->delete();

    return response()->noContent();
  }
}

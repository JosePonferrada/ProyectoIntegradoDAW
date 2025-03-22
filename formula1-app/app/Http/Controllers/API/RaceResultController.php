<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RaceResultResource;
use App\Models\RaceResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RaceResultController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    $query = RaceResult::query()->with(['race', 'driver', 'constructor']);

    if ($request->has('race_id')) {
      $query->where('race_id', $request->race_id);
    }

    return RaceResultResource::collection(
      $query->orderBy('position_order')->get()
    );
  }

  public function store(Request $request): RaceResultResource
  {
    $validated = $request->validate([
      'race_id' => 'required|exists:races,race_id',
      'driver_id' => 'required|exists:drivers,driver_id',
      'constructor_id' => 'required|exists:constructors,constructor_id',
      'grid_position' => 'nullable|integer',
      'position' => 'nullable|integer',
      'position_text' => 'nullable|string|max:10',
      'position_order' => 'nullable|integer',
      'points' => 'required|numeric',
      'laps' => 'nullable|integer',
      'race_time' => 'nullable',
      'fastest_lap' => 'nullable|integer',
      'fastest_lap_time' => 'nullable',
      'fastest_lap_speed' => 'nullable|numeric',
      'status' => 'nullable|string|max:50',
    ]);

    $raceResult = RaceResult::create($validated);

    return new RaceResultResource($raceResult->load(['race', 'driver', 'constructor']));
  }

  public function show(RaceResult $raceResult): RaceResultResource
  {
    return new RaceResultResource($raceResult->load(['race', 'driver', 'constructor']));
  }

  public function update(Request $request, RaceResult $raceResult): RaceResultResource
  {
    $validated = $request->validate([
      'race_id' => 'sometimes|required|exists:races,race_id',
      'driver_id' => 'sometimes|required|exists:drivers,driver_id',
      'constructor_id' => 'sometimes|required|exists:constructors,constructor_id',
      'grid_position' => 'nullable|integer',
      'position' => 'nullable|integer',
      'position_text' => 'nullable|string|max:10',
      'position_order' => 'nullable|integer',
      'points' => 'sometimes|required|numeric',
      'laps' => 'nullable|integer',
      'race_time' => 'nullable',
      'fastest_lap' => 'nullable|integer',
      'fastest_lap_time' => 'nullable',
      'fastest_lap_speed' => 'nullable|numeric',
      'status' => 'nullable|string|max:50',
    ]);

    $raceResult->update($validated);

    return new RaceResultResource($raceResult->load(['race', 'driver', 'constructor']));
  }

  public function destroy(RaceResult $raceResult): Response
  {
    $raceResult->delete();

    return response()->noContent();
  }
}

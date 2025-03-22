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
    $query = DriverStanding::query()->with(['season', 'driver', 'race']);

    if ($request->has('season_id')) {
      $query->where('season_id', $request->season_id);
    }

    if ($request->has('race_id')) {
      $query->where('race_id', $request->race_id);
    }

    return DriverStandingResource::collection(
      $query->orderBy('position')->get()
    );
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
}

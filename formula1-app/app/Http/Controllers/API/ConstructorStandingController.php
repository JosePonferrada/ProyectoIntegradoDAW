<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstructorStandingResource;
use App\Models\ConstructorStanding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ConstructorStandingController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    $query = ConstructorStanding::query()->with(['season', 'constructor', 'race']);

    if ($request->has('season_id')) {
      $query->where('season_id', $request->season_id);
    }

    if ($request->has('race_id')) {
      $query->where('race_id', $request->race_id);
    }

    return ConstructorStandingResource::collection(
      $query->orderBy('position')->get()
    );
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
}

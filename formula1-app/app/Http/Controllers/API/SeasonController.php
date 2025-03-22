<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeasonResource;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SeasonController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return SeasonResource::collection(
      Season::with(['championDriver', 'championConstructor'])->orderBy('year', 'desc')->get()
    );
  }

  public function store(Request $request): SeasonResource
  {
    $validated = $request->validate([
      'year' => 'required|digits:4|unique:seasons,year',
      'races_count' => 'nullable|integer',
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'reigning_champion_driver' => 'nullable|exists:drivers,driver_id',
      'reigning_champion_constructor' => 'nullable|exists:constructors,constructor_id',
      'regulation_changes' => 'nullable|string',
    ]);

    $season = Season::create($validated);

    return new SeasonResource($season->load(['championDriver', 'championConstructor']));
  }

  public function show(Season $season): SeasonResource
  {
    return new SeasonResource($season->load([
      'championDriver',
      'championConstructor',
      'races.circuit',
      'races.results'
    ]));
  }

  public function update(Request $request, Season $season): SeasonResource
  {
    $validated = $request->validate([
      'year' => 'sometimes|required|digits:4|unique:seasons,year,' . $season->season_id . ',season_id',
      'races_count' => 'nullable|integer',
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'reigning_champion_driver' => 'nullable|exists:drivers,driver_id',
      'reigning_champion_constructor' => 'nullable|exists:constructors,constructor_id',
      'regulation_changes' => 'nullable|string',
    ]);

    $season->update($validated);

    return new SeasonResource($season->load(['championDriver', 'championConstructor']));
  }

  public function destroy(Season $season): Response
  {
    $season->delete();

    return response()->noContent();
  }
}

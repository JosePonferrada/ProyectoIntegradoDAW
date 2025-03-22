<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstructorResource;
use App\Models\Constructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ConstructorController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return ConstructorResource::collection(Constructor::with('country')->get());
  }

  public function store(Request $request): ConstructorResource
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'nationality' => 'nullable|exists:countries,country_id',
      'logo' => 'nullable|string|max:255',
      'base' => 'nullable|string|max:255',
      'first_team_entry' => 'nullable|digits:4',
      'team_chief' => 'nullable|string|max:255',
      'technical_chief' => 'nullable|string|max:255',
      'chassis' => 'nullable|string|max:255',
      'power_unit' => 'nullable|string|max:255',
      'official_website' => 'nullable|string|max:255',
    ]);

    $constructor = Constructor::create($validated);

    return new ConstructorResource($constructor->load('country'));
  }

  public function show(Constructor $constructor): ConstructorResource
  {
    return new ConstructorResource($constructor->load(['country', 'drivers']));
  }

  public function update(Request $request, Constructor $constructor): ConstructorResource
  {
    $validated = $request->validate([
      'name' => 'sometimes|required|string|max:255',
      'nationality' => 'nullable|exists:countries,country_id',
      'logo' => 'nullable|string|max:255',
      'base' => 'nullable|string|max:255',
      'first_team_entry' => 'nullable|digits:4',
      'team_chief' => 'nullable|string|max:255',
      'technical_chief' => 'nullable|string|max:255',
      'chassis' => 'nullable|string|max:255',
      'power_unit' => 'nullable|string|max:255',
      'official_website' => 'nullable|string|max:255',
    ]);

    $constructor->update($validated);

    return new ConstructorResource($constructor->load('country'));
  }

  public function destroy(Constructor $constructor): Response
  {
    $constructor->delete();

    return response()->noContent();
  }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CircuitResource;
use App\Models\Circuit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CircuitController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return CircuitResource::collection(Circuit::with('country')->get());
  }

  public function store(Request $request): CircuitResource
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'location' => 'required|string|max:255',
      'country_id' => 'nullable|exists:countries,country_id',
      'length' => 'required|numeric',
      'lap_record' => 'nullable|date_format:H:i:s.v',
      'lap_record_driver' => 'nullable|string|max:255',
      'lap_record_year' => 'nullable|digits:4',
      'map_image' => 'nullable|string|max:255',
      'description' => 'nullable|string'
    ]);

    $circuit = Circuit::create($validated);

    return new CircuitResource($circuit->load('country'));
  }

  public function show(Circuit $circuit): CircuitResource
  {
    return new CircuitResource($circuit->load('country'));
  }

  public function update(Request $request, Circuit $circuit): CircuitResource
  {
    $validated = $request->validate([
      'name' => 'sometimes|required|string|max:255',
      'location' => 'sometimes|required|string|max:255',
      'country_id' => 'nullable|exists:countries,country_id',
      'length' => 'sometimes|required|numeric',
      'lap_record' => 'nullable|date_format:H:i:s.v',
      'lap_record_driver' => 'nullable|string|max:255',
      'lap_record_year' => 'nullable|digits:4',
      'map_image' => 'nullable|string|max:255',
      'description' => 'nullable|string'
    ]);

    $circuit->update($validated);

    return new CircuitResource($circuit->load('country'));
  }

  public function destroy(Circuit $circuit): Response
  {
    $circuit->delete();

    return response()->noContent();
  }
}

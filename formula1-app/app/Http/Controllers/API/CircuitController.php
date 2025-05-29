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
      'map_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
      'layout_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
      'description' => 'nullable|string'
    ]);

    if ($request->hasFile('map_image')) {
        $validated['map_image'] = $request->file('map_image')->store('circuits', 'public');
    }
    if ($request->hasFile('layout_image')) {
        $validated['layout_image'] = $request->file('layout_image')->store('circuits/layouts', 'public');
    }

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
      'map_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
      'layout_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
      'description' => 'nullable|string'
    ]);

    // Manejar eliminación explícita de imágenes (cuando se hace clic en la X)
    if ($request->has('remove_map_image') && $circuit->map_image) {
        \Storage::disk('public')->delete($circuit->map_image);
        $validated['map_image'] = null;
    }

    if ($request->has('remove_layout_image') && $circuit->layout_image) {
        \Storage::disk('public')->delete($circuit->layout_image);
        $validated['layout_image'] = null;
    }

    // Manejar reemplazo de imágenes (cuando se sube una nueva)
    if ($request->hasFile('map_image')) {
        if ($circuit->map_image) {
            \Storage::disk('public')->delete($circuit->map_image);
        }
        $validated['map_image'] = $request->file('map_image')->store('circuits', 'public');
    }
    
    if ($request->hasFile('layout_image')) {
        if ($circuit->layout_image) {
            \Storage::disk('public')->delete($circuit->layout_image);
        }
        $validated['layout_image'] = $request->file('layout_image')->store('circuits/layouts', 'public');
    }

    $circuit->update($validated);

    return new CircuitResource($circuit->load('country'));
  }

  public function destroy(Circuit $circuit): Response
  {
    $circuit->delete();

    return response()->noContent();
  }
}

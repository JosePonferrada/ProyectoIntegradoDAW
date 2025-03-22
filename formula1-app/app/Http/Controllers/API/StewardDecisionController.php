<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StewardDecisionResource;
use App\Models\StewardDecision;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StewardDecisionController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    $query = StewardDecision::query()->with('race');

    if ($request->has('race_id')) {
      $query->where('race_id', $request->race_id);
    }

    return StewardDecisionResource::collection($query->get());
  }

  public function store(Request $request): StewardDecisionResource
  {
    $validated = $request->validate([
      'race_id' => 'nullable|exists:races,race_id',
      'document_path' => 'required|string|max:255',
      'upload_date' => 'nullable|date',
      'description' => 'nullable|string',
    ]);

    $stewardDecision = StewardDecision::create($validated);

    return new StewardDecisionResource($stewardDecision->load('race'));
  }

  public function show(StewardDecision $stewardDecision): StewardDecisionResource
  {
    return new StewardDecisionResource($stewardDecision->load('race'));
  }

  public function update(Request $request, StewardDecision $stewardDecision): StewardDecisionResource
  {
    $validated = $request->validate([
      'race_id' => 'nullable|exists:races,race_id',
      'document_path' => 'sometimes|required|string|max:255',
      'upload_date' => 'nullable|date',
      'description' => 'nullable|string',
    ]);

    $stewardDecision->update($validated);

    return new StewardDecisionResource($stewardDecision->load('race'));
  }

  public function destroy(StewardDecision $stewardDecision): Response
  {
    $stewardDecision->delete();

    return response()->noContent();
  }
}

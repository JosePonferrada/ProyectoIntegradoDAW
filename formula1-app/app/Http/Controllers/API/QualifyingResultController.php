<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\QualifyingResultResource;
use App\Models\QualifyingResult;
use App\Models\Race;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class QualifyingResultController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    $query = QualifyingResult::query()->with(['race', 'driver', 'constructor']);

    if ($request->has('race_id')) {
      $query->where('race_id', $request->race_id);
    }

    return QualifyingResultResource::collection(
      $query->orderBy('position')->get()
    );
  }

  public function store(Request $request): QualifyingResultResource
  {
    $validated = $request->validate([
      'race_id' => 'required|exists:races,race_id',
      'driver_id' => 'required|exists:drivers,driver_id',
      'constructor_id' => 'required|exists:constructors,constructor_id',
      'position' => 'nullable|integer|min:1',
      'q1_time' => 'nullable',
      'q2_time' => 'nullable',
      'q3_time' => 'nullable',
    ]);

    $qualifyingResult = QualifyingResult::create($validated);

    return new QualifyingResultResource($qualifyingResult->load(['race', 'driver', 'constructor']));
  }

  public function show(QualifyingResult $qualifyingResult): QualifyingResultResource
  {
    return new QualifyingResultResource($qualifyingResult->load(['race', 'driver', 'constructor']));
  }

  public function update(Request $request, QualifyingResult $qualifyingResult): QualifyingResultResource
  {
    $validated = $request->validate([
      'race_id' => 'sometimes|required|exists:races,race_id',
      'driver_id' => 'sometimes|required|exists:drivers,driver_id',
      'constructor_id' => 'sometimes|required|exists:constructors,constructor_id',
      'position' => 'nullable|integer|min:1',
      'q1_time' => 'nullable',
      'q2_time' => 'nullable',
      'q3_time' => 'nullable',
    ]);

    $qualifyingResult->update($validated);

    return new QualifyingResultResource($qualifyingResult->load(['race', 'driver', 'constructor']));
  }

  public function destroy(QualifyingResult $qualifyingResult): Response
  {
    $qualifyingResult->delete();

    return response()->noContent();
  }
}

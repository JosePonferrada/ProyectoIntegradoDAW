<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\QualifyingResultResource;
use App\Models\QualifyingResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

  /**
   * Store or update multiple qualifying results for a race.
   * This method will delete all existing qualifying results for the race
   * and then create new ones based on the provided data.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function bulkStore(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'race_id' => 'required|integer|exists:races,race_id',
      'results' => 'required|array',
      'results.*.driver_id' => 'required|integer|exists:drivers,driver_id',
      'results.*.constructor_id' => 'required|integer|exists:constructors,constructor_id',
      'results.*.position' => 'required|integer|min:1',
      'results.*.q1_time' => ['nullable', 'regex:/^(?:00:)?([0-5]?\d):([0-5]?\d)\.\d{1,3}$/'],
      'results.*.q2_time' => ['nullable', 'regex:/^(?:00:)?([0-5]?\d):([0-5]?\d)\.\d{1,3}$/'],
      'results.*.q3_time' => ['nullable', 'regex:/^(?:00:)?([0-5]?\d):([0-5]?\d)\.\d{1,3}$/'],
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $raceId = $request->input('race_id');
    $resultsData = $request->input('results');

    DB::beginTransaction();
    try {
      // 1. Delete all existing qualifying results for this race
      QualifyingResult::where('race_id', $raceId)->delete();

      // 2. Create new qualifying results from the provided data
      foreach ($resultsData as $resultItem) {
        $dataToCreate = [
          'race_id' => $raceId,
          'driver_id' => $resultItem['driver_id'],
          'constructor_id' => $resultItem['constructor_id'],
          'position' => $resultItem['position'],
          'q1_time' => $this->formatQualifyingTime($resultItem['q1_time'] ?? null),
          'q2_time' => $this->formatQualifyingTime($resultItem['q2_time'] ?? null),
          'q3_time' => $this->formatQualifyingTime($resultItem['q3_time'] ?? null),
        ];

        QualifyingResult::create($dataToCreate);
      }

      DB::commit();
      return response()->json(['message' => 'Qualifying results saved successfully.'], 201);

    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error('Error in bulkStore qualifying results: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
      return response()->json(['message' => 'An error occurred while saving qualifying results.', 'error' => $e->getMessage()], 500);
    }
  }

  /**
   * Helper function to format qualifying time.
   * Accepts MM:SS.m, MM:SS.mm, MM:SS.mmm or HH:MM:SS.m, HH:MM:SS.mm, HH:MM:SS.mmm
   * Normalizes to HH:MM:SS.mmm format, padding milliseconds to 3 digits.
   * Returns null if input is null, empty, or an unrecognized format.
   *
   * @param string|null $time
   * @return string|null
   */
  private function formatQualifyingTime(?string $time): ?string
  {
    if (empty($time)) {
      return null;
    }

    // Intenta coincidir con MM:SS.m{1,3} (ej. 1:17.2, 1:17.29, 1:17.290)
    // $matches[1] = minutos, $matches[2] = segundos, $matches[3] = milisegundos (1 a 3 dígitos)
    if (preg_match('/^([0-5]?\d):([0-5]?\d)\.(\d{1,3})$/', $time, $matches)) {
      $minutes = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
      $seconds = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
      // Rellena los milisegundos con ceros a la DERECHA hasta que tengan 3 dígitos
      $milliseconds = str_pad($matches[3], 3, '0', STR_PAD_RIGHT); 
      return '00:' . $minutes . ':' . $seconds . '.' . $milliseconds;
    }

    // Intenta coincidir con HH:MM:SS.m{1,3} (ej. 00:01:17.2, 01:17:29.12, 0:01:17.123)
    // $matches_hh[1] = horas, $matches_hh[2] = minutos, $matches_hh[3] = segundos, $matches_hh[4] = milisegundos
    if (preg_match('/^(\d{1,2}):([0-5]?\d):([0-5]?\d)\.(\d{1,3})$/', $time, $matches_hh)) {
        $hours = str_pad($matches_hh[1], 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($matches_hh[2], 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($matches_hh[3], 2, '0', STR_PAD_LEFT);
        // Rellena los milisegundos con ceros a la DERECHA
        $milliseconds = str_pad($matches_hh[4], 3, '0', STR_PAD_RIGHT);
        return $hours . ':' . $minutes . ':' . $seconds . '.' . $milliseconds;
    }
    
    // Si el formato no coincide con ninguno de los esperados, registra una advertencia y devuelve null
    \Log::warning('Invalid qualifying time format received, storing as null:', ['time_received' => $time]);
    return null; 
  }
}

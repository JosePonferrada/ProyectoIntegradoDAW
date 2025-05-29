<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\RaceResultResource;
use App\Models\RaceResult;
use App\Models\Race;
use App\Events\RaceResultsFinalized;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
      'race_time' => ['nullable', 'string', 'regex:/^(?:(\d{1,2}):)?([0-5]?\d):([0-5]?\d)\.(\d{3})$/'], // HH:MM:SS.mmm o MM:SS.mmm
      'fastest_lap' => 'nullable|integer',
      'fastest_lap_time' => ['nullable', 'string', 'regex:/^([0-5]?\d):([0-5]?\d)\.(\d{1,3})$/'], // MM:SS.mmm
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
      'race_time' => ['nullable', 'string', 'regex:/^(?:(\d{1,2}):)?([0-5]?\d):([0-5]?\d)\.(\d{3})$/'],
      'fastest_lap' => 'nullable|integer',
      'fastest_lap_time' => ['nullable', 'string', 'regex:/^(?:0:)?([0-5]?\d):([0-5]?\d)\.(\d{3})$/'],
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

  public function storeBulk(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'race_id' => 'required|integer|exists:races,race_id',
        'results' => 'required|array',
        'results.*.driver_id' => 'required|integer|exists:drivers,driver_id',
        'results.*.constructor_id' => 'required|integer|exists:constructors,constructor_id',
        'results.*.position' => 'required|integer|min:1',
        'results.*.position_text' => 'required|string|max:10',
        'results.*.position_order' => 'required|integer|min:1',
        'results.*.grid_position' => 'nullable|integer|min:1',
        'results.*.laps' => 'required|integer|min:0',
        'results.*.points' => 'required|numeric|min:0',
        'results.*.status' => 'required|string|max:50',
        'results.*.race_time' => ['nullable', 'string', 'regex:/^(?:(\d{1,2}):)?([0-5]?\d):([0-5]?\d)\.(\d{3})$/'],
        'results.*.fastest_lap' => 'nullable|integer',
        'results.*.fastest_lap_time' => ['nullable', 'string', 'regex:/^([0-5]?\d):([0-5]?\d)\.(\d{1,3})$/'],
        'results.*.fastest_lap_speed' => 'nullable|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $raceId = $request->input('race_id');
    $resultsData = $request->input('results');

    DB::beginTransaction();
    try {
        $race = Race::findOrFail($raceId);

        RaceResult::where('race_id', $raceId)->delete();

        foreach ($resultsData as $resultItem) {
            // Para fastest_lap_time, AÑADIR explícitamente el 00: que necesita MySQL TIME
            if (isset($resultItem['fastest_lap_time']) && $resultItem['fastest_lap_time']) {
                // Si viene en formato MM:SS.mmm (sin 00:), añadírselo para que MySQL lo acepte
                if (preg_match('/^([0-5]?\d):([0-5]?\d)\.(\d{1,3})$/', $resultItem['fastest_lap_time'], $matches)) {
                    // Añadir 00: al principio para que MySQL lo interprete correctamente
                    $resultItem['fastest_lap_time'] = '00:' . str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . 
                                                      str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '.' . 
                                                      str_pad($matches[3], 3, '0', STR_PAD_RIGHT);
                }
            }

            // Para race_time, mantener el código que ya tienes
            if (isset($resultItem['race_time']) && $resultItem['race_time']) {
                if (preg_match('/^([0-5]?\d):([0-5]?\d)\.(\d{3})$/', $resultItem['race_time'], $matches)) {
                    $resultItem['race_time'] = '00:' . str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . 
                                               str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '.' . $matches[3];
                }
            }

            RaceResult::create(array_merge($resultItem, ['race_id' => $raceId]));
        }

        if ($race->status !== 'Completed') {
            $race->status = 'Completed';
            $race->save();
        }
        
        DB::commit();

        if ($race->status === 'Completed') {
            Log::info("Dispatching RaceResultsFinalized event for Race ID: {$race->race_id}");
            RaceResultsFinalized::dispatch($race);
        }

        return response()->json(['message' => 'Race results saved successfully. Prediction points calculation initiated.'], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to save race results for Race ID ' . $raceId . ': ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
        return response()->json(['message' => 'Failed to save race results.', 'error' => $e->getMessage()], 500);
    }
  }
}

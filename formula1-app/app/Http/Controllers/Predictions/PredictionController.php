<?php

namespace App\Http\Controllers\Predictions;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RacePrediction;
use App\Models\PredictionPosition;
use App\Models\RaceResult;
use App\Models\QualifyingResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon; // Asegúrate de tener este import
use App\Http\Resources\RaceResource; // Mantén este si lo usas para upcomingRaces

class PredictionController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $upcomingRacesQuery = Race::query()
            ->with(['circuit.country', 'season']) // Asumiendo que circuit tiene relación country
            ->where(function ($query) use ($now) {
                $query->where('race_date', '>', $now->toDateString())
                      ->orWhere(function ($query) use ($now) {
                          $query->where('race_date', '=', $now->toDateString())
                                ->whereNotNull('start_time')
                                ->where('start_time', '>', $now->toTimeString());
                      });
            })
            ->orderBy('race_date', 'asc')
            ->orderBy('start_time', 'asc');

        $upcomingRaces = $upcomingRacesQuery->get();
        
        $userPredictions = RacePrediction::where('user_id', auth()->id())
            ->with([
                'race' => function ($query) {
                    $query->with([
                        'circuit.country', // Asumiendo que circuit tiene relación country
                        'results' => function ($query) {
                            $query->with(['driver']);
                        }, 
                    ]);
                },
                'positions.driver',
                'fastestLapDriver' // Este es el piloto PREDICHO por el usuario para la vuelta rápida
            ])
            ->orderByDesc('created_at') // O por fecha de carrera, según prefieras
            ->get();
            
        return Inertia::render('Predictions/Index', [
            'upcomingRaces' => RaceResource::collection($upcomingRaces), // Usa RaceResource si lo tienes para formatear las carreras
            'userPredictions' => $userPredictions // <-- CORREGIDO: Pasar directamente la colección
        ]);
    }
    
    public function create($race)
    {
        // Manually find the race using race_id
        \Log::info('[PredictionController@create] Finding race with route key: ' . $race);
        $race = Race::where('race_id', $race)->firstOrFail();
        \Log::info('[PredictionController@create] Found Race: ID=' . $race->race_id . ', Name=' . $race->name . ', Date=' . $race->race_date . ', Time=' . $race->start_time);
        
        try {
            // Extraer solo la parte de fecha de race_date
            $raceDate = date('Y-m-d', strtotime($race->race_date));
            
            // Extraer solo la parte de tiempo de start_time (ignorar la fecha si la tiene)
            $raceDateTime = null; // Inicializar
            if ($race->start_time) {
                $timeInfo = date_parse($race->start_time);
                $timeString = sprintf('%02d:%02d:00', $timeInfo['hour'], $timeInfo['minute']);
                $raceDateTimeString = $raceDate . ' ' . $timeString;
                $raceDateTime = new \DateTime($raceDateTimeString);
                \Log::info('[PredictionController@create] Parsed Race DateTime: ' . $raceDateTime->format('Y-m-d H:i:s'));
            } else {
                // Si no hay hora de inicio, usar solo la fecha (medianoche)
                $raceDateTime = new \DateTime($raceDate); // Esto será Y-m-d 00:00:00
                \Log::info('[PredictionController@create] Parsed Race Date (no start_time, using midnight): ' . $raceDateTime->format('Y-m-d H:i:s'));
            }
            
            $nowDateTime = now();
            \Log::info('[PredictionController@create] Current DateTime (now()): ' . $nowDateTime->format('Y-m-d H:i:s'));
            \Log::info('[PredictionController@create] Comparing for lock: now() >= raceDateTime ? ' . ($nowDateTime->greaterThanOrEqualTo($raceDateTime) ? 'YES, LOCKED' : 'NO, OPEN'));

            // Ahora compara correctamente
            if ($nowDateTime->greaterThanOrEqualTo($raceDateTime)) {
                \Log::warning('[PredictionController@create] LOCK APPLIED: Race has started or time passed. Redirecting.');
                return redirect()->route('predictions.index')
                    ->with('error', 'Predictions are locked for this race as it has already started.');
            }
        } catch (\Exception $e) {
            \Log::error('[PredictionController@create] Error parsing race date/time: ' . $e->getMessage());
        }
        
        // Verify race hasn't happened yet (esta es una comprobación más general por día)
        $currentDateOnly = now()->startOfDay(); // Solo la fecha actual, sin hora
        $raceDateOnly = Carbon::parse($race->race_date)->startOfDay(); // Solo la fecha de la carrera, sin hora

        \Log::info('[PredictionController@create] Comparing for past race (date only): race_date (' . $raceDateOnly->toDateString() . ') < now_date_only (' . $currentDateOnly->toDateString() . ') ? ' . ($raceDateOnly->lessThan($currentDateOnly) ? 'YES, PAST' : 'NO, NOT PAST'));
        if ($raceDateOnly->lessThan($currentDateOnly)) {
            \Log::warning('[PredictionController@create] PAST RACE LOCK: Race date is in the past. Redirecting.');
            return redirect()->route('predictions.index')
                ->with('error', 'Predictions cannot be made for past races.');
        }
        
        // Verify if user already has prediction for this race
        $existingPrediction = RacePrediction::where('user_id', auth()->id())
            ->where('race_id', $race->race_id)
            ->with(['positions.driver', 'fastestLapDriver'])
            ->first();
        
        // Get only current year's drivers
        $currentYear = date('Y');
        $drivers = Driver::whereHas('seasons', function($query) use ($currentYear) {
                $query->where('year', $currentYear);
            })
            ->with('currentTeam')  // Make sure this relationship is loaded
            ->orderBy('last_name')
            ->get();
        
        return Inertia::render('Predictions/Create', [
            'race' => $race->load('circuit'),
            'drivers' => $drivers,
            'existingPrediction' => $existingPrediction
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'race_id' => 'required|exists:races,race_id', 
            'positions' => 'required|array|size:10',
            'positions.*' => 'required|exists:drivers,driver_id|distinct', 
            'dnf_count' => 'required|integer|min:0|max:20',
            'fastest_lap_driver_id' => 'required|exists:drivers,driver_id', 
        ]);
        
        $race = Race::where('race_id', $validated['race_id'])->firstOrFail();
        \Log::info('[PredictionController@store] Storing prediction for Race: ID=' . $race->race_id . ', Name=' . $race->name . ', Date=' . $race->race_date . ', Time=' . $race->start_time);

        try {
            // Extraer solo la parte de fecha de race_date
            $raceDate = date('Y-m-d', strtotime($race->race_date));
            
            $raceDateTime = null; // Inicializar
            // Extraer solo la parte de tiempo de start_time (ignorar la fecha si la tiene)
            if ($race->start_time) {
                $timeInfo = date_parse($race->start_time);
                $timeString = sprintf('%02d:%02d:00', $timeInfo['hour'], $timeInfo['minute']);
                $raceDateTimeString = $raceDate . ' ' . $timeString;
                $raceDateTime = new \DateTime($raceDateTimeString);
                \Log::info('[PredictionController@store] Parsed Race DateTime: ' . $raceDateTime->format('Y-m-d H:i:s'));
            } else {
                // Si no hay hora de inicio, usar solo la fecha (medianoche)
                $raceDateTime = new \DateTime($raceDate);
                \Log::info('[PredictionController@store] Parsed Race Date (no start_time, using midnight): ' . $raceDateTime->format('Y-m-d H:i:s'));
            }
            
            $nowDateTime = now();
            \Log::info('[PredictionController@store] Current DateTime (now()): ' . $nowDateTime->format('Y-m-d H:i:s'));
            \Log::info('[PredictionController@store] Comparing for lock: now() >= raceDateTime ? ' . ($nowDateTime->greaterThanOrEqualTo($raceDateTime) ? 'YES, LOCKED' : 'NO, OPEN'));
            
            // Ahora compara correctamente
            if ($nowDateTime->greaterThanOrEqualTo($raceDateTime)) {
                \Log::warning('[PredictionController@store] LOCK APPLIED: Race has started or time passed. Redirecting.');
                return redirect()->route('predictions.index') // Cambiado a redirect para consistencia con create
                    ->with('error', 'Predictions are locked for this race as it has already started.');
            }
        } catch (\Exception $e) {
            \Log::error('[PredictionController@store] Error parsing race date/time: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error processing date/time information.']);
        }
        
        // Verify race hasn't happened yet (esta es una comprobación más general por día)
        $currentDateOnly = now()->startOfDay(); // Solo la fecha actual, sin hora
        $raceDateOnly = Carbon::parse($race->race_date)->startOfDay(); // Solo la fecha de la carrera, sin hora

        \Log::info('[PredictionController@store] Comparing for past race (date only): race_date (' . $raceDateOnly->toDateString() . ') < now_date_only (' . $currentDateOnly->toDateString() . ') ? ' . ($raceDateOnly->lessThan($currentDateOnly) ? 'YES, PAST' : 'NO, NOT PAST'));
        if ($raceDateOnly->lessThan($currentDateOnly)) {
            \Log::warning('[PredictionController@store] PAST RACE LOCK: Race date is in the past. Redirecting.');
            return back()->withErrors(['race' => 'Predictions cannot be made for past races.']);
        }
        
        DB::beginTransaction();
        
        try {
            // Create or update the main prediction
            $prediction = RacePrediction::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'race_id' => $validated['race_id']
                ],
                [
                    'dnf_count' => $validated['dnf_count'],
                    'fastest_lap_driver_id' => $validated['fastest_lap_driver_id']
                ]
            );
            
            // Delete existing positions if any
            $prediction->positions()->delete();
            
            // Create the prediction positions
            foreach ($validated['positions'] as $index => $driverId) {
                $prediction->positions()->create([
                    'driver_id' => $driverId,
                    'position' => $index + 1
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('predictions.index')
                ->with('success', 'Prediction saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred while saving your prediction: ' . $e->getMessage()]);
        }
    }
    
    public function leaderboard()
    {
        $topUsers = \App\Models\User::withSum('predictions as total_points', 'points')
            ->orderByDesc('total_points')
            ->take(20)
            ->get();
            
        $currentUserRank = \App\Models\User::withSum('predictions as total_points', 'points')
            ->orderByDesc('total_points')
            ->get()
            ->search(function($user) {
                return $user->id === auth()->id();
            }) + 1;
            
        return Inertia::render('Predictions/Leaderboard', [
            'topUsers' => $topUsers,
            'currentUserRank' => $currentUserRank
        ]);
    }
    
    // Método para calcular puntos después de una carrera
    public function calculatePoints(Race $race)
    {
        // Verify race has finished
        if ($race->race_date > now()) {
            return back()->with('error', 'Race has not finished yet.');
        }
        
        $predictions = RacePrediction::where('race_id', $race->race_id)->get();
        $raceResults = RaceResult::where('race_id', $race->race_id)
            ->orderBy('position')
            ->get();
        $qualifyingResults = QualifyingResult::where('race_id', $race->race_id)
            ->orderBy('position')
            ->get();
        $actualDNFs = RaceResult::where('race_id', $race->race_id)
            ->whereNotNull('status')
            ->whereNot('status', 'Finished')
            ->count();
        
        foreach ($predictions as $prediction) {
            $points = 0;
            
            // Points for positions
            foreach ($prediction->positions as $predictedPosition) {
                $actualResult = $raceResults->firstWhere('driver_id', $predictedPosition->driver_id);
                
                if ($actualResult && $actualResult->position == $predictedPosition->position) {
                    // Position is correct
                    if ($predictedPosition->position == 1) {
                        $points += 3; // P1 = 3 points
                    } else {
                        $points += 1; // Other positions = 1 point
                    }
                }
            }
            
            // Check for complete podium
            $correctPodium = true;
            for ($position = 1; $position <= 3; $position++) {
                $predictedDriver = $prediction->positions->firstWhere('position', $position);
                $actualDriver = $raceResults->firstWhere('position', $position);
                
                if (!$predictedDriver || !$actualDriver || $predictedDriver->driver_id != $actualDriver->driver_id) {
                    $correctPodium = false;
                    break;
                }
            }
            
            if ($correctPodium) {
                $points += 10; // Complete podium = 10 points
            }
            
            // Points for pole position (P1 in qualifying)
            $poleDriver = $qualifyingResults->firstWhere('position', 1);
            $predictedPole = $prediction->positions->firstWhere('position', 1);
            
            if ($poleDriver && $predictedPole && $poleDriver->driver_id == $predictedPole->driver_id) {
                $points += 2; // Pole position correct = 2 points
            }
            
            // Points for fastest lap
            if ($prediction->fastest_lap_driver_id == $race->fastest_lap_driver_id) {
                $points += 1; // Fastest lap correct = 1 point
            }
            
            // Points for DNFs
            $diff = abs($prediction->dnf_count - $actualDNFs);
            if ($prediction->dnf_count < $actualDNFs) {
                // Prediction is less than actual DNFs
                $points += max(0, 10 - $diff);
            } else {
                // Prediction is greater than or equal to actual DNFs
                $points += max(0, 10 - ($diff * 2));
            }
            
            // Update points
            $prediction->update(['points' => $points, 'is_locked' => true]);
        }
        
        return back()->with('success', 'Points calculated successfully.');
    }
}

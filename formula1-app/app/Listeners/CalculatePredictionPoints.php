<?php

namespace App\Listeners;

use App\Events\RaceResultsFinalized;
use App\Models\RacePrediction;
use App\Models\RaceResult;
use App\Models\QualifyingResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CalculatePredictionPoints
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RaceResultsFinalized $event): void
    {
        $race = $event->race;
        Log::info("[CalculatePredictionPoints] Calculating points for Race ID: {$race->race_id} - {$race->name}");

        $predictions = RacePrediction::where('race_id', $race->race_id)->with('positions')->get();
        
        if ($predictions->isEmpty()) {
            Log::info("[CalculatePredictionPoints] No predictions found for Race ID: {$race->race_id}.");
            return;
        }

        $raceResults = RaceResult::where('race_id', $race->race_id)
            ->orderBy('position')
            ->get();
        
        if ($raceResults->isEmpty()) {
            Log::warning("[CalculatePredictionPoints] No race results found for Race ID: {$race->race_id}. Cannot calculate points.");
            return;
        }
            
        $qualifyingResults = QualifyingResult::where('race_id', $race->race_id)
            ->orderBy('position')
            ->get();

        $dnfStatuses = ['DNS', 'DNF'];

        $actualDNFs = RaceResult::where('race_id', $race->race_id)
            ->whereIn('status', $dnfStatuses)
            ->count();
        
        Log::info("[CalculatePredictionPoints] Race ID {$race->race_id}: Actual DNFs based on status list = {$actualDNFs}");

        $actualFastestLapDriverId = null;
        if ($raceResults->isNotEmpty()) {
            $resultsWithFLTime = $raceResults->filter(function ($result) {
                return !empty($result->fastest_lap_time);
            });

            if ($resultsWithFLTime->isNotEmpty()) {
                $bestFastestLapResult = $resultsWithFLTime->sortBy('fastest_lap_time')->first();
                if ($bestFastestLapResult) {
                    $actualFastestLapDriverId = $bestFastestLapResult->driver_id;
                    Log::info("[CalculatePredictionPoints] Race ID {$race->race_id}: Actual fastest lap driver ID = {$actualFastestLapDriverId} with time {$bestFastestLapResult->fastest_lap_time}.");
                }
            }
        }
        if (!$actualFastestLapDriverId) {
            Log::warning("[CalculatePredictionPoints] Race ID {$race->race_id}: No actual fastest lap driver found based on fastest_lap_time.");
        }
        
        foreach ($predictions as $prediction) {
            $points = 0;
            Log::info("[CalculatePredictionPoints] Processing Prediction ID: {$prediction->id} for User ID: {$prediction->user_id}");
            
            foreach ($prediction->positions as $predictedPosition) {
                $actualResultForDriver = $raceResults->firstWhere('driver_id', $predictedPosition->driver_id);
                
                if ($actualResultForDriver && $actualResultForDriver->position == $predictedPosition->position) {
                    if ($predictedPosition->position == 1) {
                        $points += 3; 
                        Log::info("-- Prediction ID {$prediction->id}: +3 points for P1 correct (Driver ID: {$predictedPosition->driver_id})");
                    } else {
                        $points += 1; 
                        Log::info("-- Prediction ID {$prediction->id}: +1 point for P{$predictedPosition->position} correct (Driver ID: {$predictedPosition->driver_id})");
                    }
                }
            }
            
            $correctPodium = true;
            for ($pos = 1; $pos <= 3; $pos++) {
                $predictedDriverInPosition = $prediction->positions->firstWhere('position', $pos);
                $actualDriverInPosition = $raceResults->firstWhere('position', $pos);
                
                if (!$predictedDriverInPosition || !$actualDriverInPosition || $predictedDriverInPosition->driver_id != $actualDriverInPosition->driver_id) {
                    $correctPodium = false;
                    break;
                }
            }
            
            if ($correctPodium) {
                $points += 10; 
                Log::info("-- Prediction ID {$prediction->id}: +10 points for correct podium.");
            }
            
            // if ($qualifyingResults->isNotEmpty()) {
            //     $poleDriverResult = $qualifyingResults->firstWhere('position', 1);
            //     $predictedPoleDriver = $prediction->positions->firstWhere('position', 1); 
                
            //     if ($poleDriverResult && $predictedPoleDriver && $poleDriverResult->driver_id == $predictedPoleDriver->driver_id) {
            //         $points += 2; 
            //         Log::info("-- Prediction ID {$prediction->id}: +2 points for correct pole (Driver ID: {$predictedPoleDriver->driver_id}).");
            //     }
            // } else {
            //     Log::warning("[CalculatePredictionPoints] Race ID {$race->race_id}: No qualifying results found for pole points calculation for Prediction ID {$prediction->id}.");
            // }
            
            if ($prediction->fastest_lap_driver_id && $actualFastestLapDriverId && $prediction->fastest_lap_driver_id == $actualFastestLapDriverId) {
                $points += 1; 
                Log::info("-- Prediction ID {$prediction->id}: +1 point for correct fastest lap (Driver ID: {$prediction->fastest_lap_driver_id}).");
            }
            
            $dnfDiff = abs($prediction->dnf_count - $actualDNFs);
            $dnfPoints = 0;
            if ($prediction->dnf_count < $actualDNFs) {
                $dnfPoints = max(0, 10 - $dnfDiff);
            } else {
                $dnfPoints = max(0, 10 - ($dnfDiff * 2));
            }
            $points += $dnfPoints;
            Log::info("-- Prediction ID {$prediction->id}: Predicted DNFs={$prediction->dnf_count}, Actual DNFs={$actualDNFs}, Diff={$dnfDiff}, DNF Points=+{$dnfPoints}.");
            
            $prediction->update(['points' => $points, 'is_locked' => true]);
            Log::info("[CalculatePredictionPoints] Prediction ID {$prediction->id} updated. Total Points = {$points}.");
        }
        Log::info("[CalculatePredictionPoints] Finished calculating points for Race ID: {$race->race_id}");
    }
}

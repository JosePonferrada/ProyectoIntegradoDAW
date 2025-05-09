<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->race_id,
      'name' => $this->name,
      'season' => new SeasonResource($this->whenLoaded('season')),
      'circuit' => new CircuitResource($this->whenLoaded('circuit')),
      'round' => $this->round,
      'race_date' => $this->race_date,
      'start_time' => $this->start_time,
      'distance' => $this->distance,
      'scheduled_laps' => $this->scheduled_laps,
      'completed_laps' => $this->completed_laps,
      'race_duration' => $this->race_duration,
      'status' => $this->status,
      'weather_conditions' => $this->weather_conditions,
      'avg_temperature' => $this->avg_temperature,
      'notes' => $this->notes,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,

      // Campos de la migración
      'practice1_date' => $this->practice1_date,
      'practice1_time' => $this->practice1_time,
      'practice2_date' => $this->practice2_date,
      'practice2_time' => $this->practice2_time,
      'practice3_date' => $this->practice3_date,
      'practice3_time' => $this->practice3_time,
      'qualifying_date' => $this->qualifying_date,
      'qualifying_time' => $this->qualifying_time,
      'sprint_qualifying_date' => $this->sprint_qualifying_date,
      'sprint_qualifying_time' => $this->sprint_qualifying_time,
      'sprint_date' => $this->sprint_date,
      'sprint_time' => $this->sprint_time,
      'weekend_format' => $this->weekend_format,

      'results' => RaceResultResource::collection($this->whenLoaded('results')),
      'qualifying_results' => QualifyingResultResource::collection($this->whenLoaded('qualifyingResults')),
      'steward_decisions' => StewardDecisionResource::collection($this->whenLoaded('stewardDecisions')),
    ];
  }
}

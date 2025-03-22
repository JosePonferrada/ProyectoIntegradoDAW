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
      'results' => RaceResultResource::collection($this->whenLoaded('results')),
      'qualifying_results' => QualifyingResultResource::collection($this->whenLoaded('qualifyingResults')),
      'steward_decisions' => StewardDecisionResource::collection($this->whenLoaded('stewardDecisions')),
    ];
  }
}

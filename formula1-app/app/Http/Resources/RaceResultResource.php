<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceResultResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->result_id,
      'race' => new RaceResource($this->whenLoaded('race')),
      'driver' => new DriverResource($this->whenLoaded('driver')),
      'constructor' => new ConstructorResource($this->whenLoaded('constructor')),
      'grid_position' => $this->grid_position,
      'position' => $this->position,
      'position_text' => $this->position_text,
      'position_order' => $this->position_order,
      'points' => $this->points,
      'laps' => $this->laps,
      'race_time' => $this->race_time,
      'fastest_lap' => $this->fastest_lap,
      'fastest_lap_time' => $this->fastest_lap_time,
      'fastest_lap_speed' => $this->fastest_lap_speed,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}

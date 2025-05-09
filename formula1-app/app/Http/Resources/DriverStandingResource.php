<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverStandingResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'season_id' => $this->season_id,
      'driver_id' => $this->driver_id,
      'race_id' => $this->race_id,
      'points' => $this->points,
      'position' => $this->position,
      'wins' => $this->wins,
      'constructor_id' => $this->when(isset($this->constructor_id), $this->constructor_id),
      'constructor_name' => $this->when(isset($this->constructor_name), $this->constructor_name),
      'constructor_logo' => $this->when(isset($this->constructor_logo), $this->constructor_logo),
      'position_number' => $this->when(isset($this->position_number), $this->position_number),
      'season' => new SeasonResource($this->whenLoaded('season')),
      'driver' => new DriverResource($this->whenLoaded('driver')),
      'race' => new RaceResource($this->whenLoaded('race')),
    ];
  }
}

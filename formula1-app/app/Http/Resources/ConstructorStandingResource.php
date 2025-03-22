<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstructorStandingResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'season' => new SeasonResource($this->whenLoaded('season')),
      'constructor' => new ConstructorResource($this->whenLoaded('constructor')),
      'race' => new RaceResource($this->whenLoaded('race')),
      'points' => $this->points,
      'position' => $this->position,
      'wins' => $this->wins,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}

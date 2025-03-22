<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->season_id,
      'year' => $this->year,
      'races_count' => $this->races_count,
      'start_date' => $this->start_date,
      'end_date' => $this->end_date,
      'champion_driver' => new DriverResource($this->whenLoaded('championDriver')),
      'champion_constructor' => new ConstructorResource($this->whenLoaded('championConstructor')),
      'regulation_changes' => $this->regulation_changes,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'races' => RaceResource::collection($this->whenLoaded('races')),
    ];
  }
}

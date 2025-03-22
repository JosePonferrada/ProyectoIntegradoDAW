<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QualifyingResultResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->qualifying_id,
      'race' => new RaceResource($this->whenLoaded('race')),
      'driver' => new DriverResource($this->whenLoaded('driver')),
      'constructor' => new ConstructorResource($this->whenLoaded('constructor')),
      'position' => $this->position,
      'q1_time' => $this->q1_time,
      'q2_time' => $this->q2_time,
      'q3_time' => $this->q3_time,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}

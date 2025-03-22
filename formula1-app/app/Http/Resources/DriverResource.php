<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->driver_id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'full_name' => $this->full_name,
      'code' => $this->code,
      'number' => $this->number,
      'date_of_birth' => $this->date_of_birth,
      'nationality' => new CountryResource($this->whenLoaded('country')),
      'active' => $this->active,
      'biography' => $this->biography,
      'image' => $this->image,
      'debut_year' => $this->debut_year,
      'championships' => $this->championships,
      'wins' => $this->wins,
      'podiums' => $this->podiums,
      'poles' => $this->poles,
      'fastest_laps' => $this->fastest_laps,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'constructors' => ConstructorResource::collection($this->whenLoaded('constructors')),
    ];
  }
}

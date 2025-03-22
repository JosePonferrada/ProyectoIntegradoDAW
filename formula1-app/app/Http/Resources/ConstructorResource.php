<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConstructorResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->constructor_id,
      'name' => $this->name,
      'nationality' => new CountryResource($this->whenLoaded('country')),
      'logo' => $this->logo,
      'base' => $this->base,
      'first_team_entry' => $this->first_team_entry,
      'team_chief' => $this->team_chief,
      'technical_chief' => $this->technical_chief,
      'chassis' => $this->chassis,
      'power_unit' => $this->power_unit,
      'official_website' => $this->official_website,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'drivers' => DriverResource::collection($this->whenLoaded('drivers')),
      'championships' => $this->whenCounted('championships'),
      'wins' => $this->whenCounted('raceResults', function () {
        return $this->raceResults->where('position', 1)->count();
      }),
    ];
  }
}

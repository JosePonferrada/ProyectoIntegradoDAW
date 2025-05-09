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
    // Get all the basic fields
    $data = [
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
      // Only include constructors when they're loaded
      'constructors' => $this->whenLoaded('constructors', function() {
          return ConstructorResource::collection($this->constructors);
      }),
    ];
    
    // Explicitly include seasons with pivot data ONLY when seasons are loaded
    if ($this->relationLoaded('seasons')) {
        try {
            $data['seasons'] = $this->seasons->map(function ($season) {
                // Get the base season data
                $seasonData = [
                    'id' => $season->id,
                    'year' => $season->year,
                    'races_count' => $season->races_count,
                    'start_date' => $season->start_date,
                    'end_date' => $season->end_date,
                ];
                
                // Add pivot data if it exists
                if ($season->pivot) {
                    $seasonData['pivot'] = [
                        'constructor_id' => $season->pivot->constructor_id,
                        'position_number' => $season->pivot->position_number
                    ];
                }
                
                return $seasonData;
            });
        } catch (\Exception $e) {
            // Log the error but don't crash
            \Log::error('Error processing seasons in DriverResource', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driver_id
            ]);
        }
    }
    
    return $data;
  }
}

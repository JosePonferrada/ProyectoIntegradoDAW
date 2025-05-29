<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CircuitResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->circuit_id,
      'name' => $this->name,
      'location' => $this->location,
      'country' => new CountryResource($this->whenLoaded('country')),
      'length' => $this->length,
      'lap_record' => $this->lap_record,
      'lap_record_driver' => $this->lap_record_driver,
      'lap_record_year' => $this->lap_record_year,
      'map_image' => $this->map_image,
      'layout_image' => $this->layout_image,
      'map_image_url' => $this->map_image ? Storage::url($this->map_image) : null,
      'layout_image_url' => $this->layout_image ? Storage::url($this->layout_image) : null,
      'description' => $this->description,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'races_count' => $this->whenLoaded('races', function () {
        return $this->races->count();
      }),
    ];
  }
}

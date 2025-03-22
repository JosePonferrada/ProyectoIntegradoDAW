<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->country_id,
      'name' => $this->name,
      'code' => $this->code,
      'flag_img' => $this->flag_img,
      'drivers_count' => $this->whenLoaded('drivers', function () {
        return $this->drivers->count();
      }),
      'circuits_count' => $this->whenLoaded('circuits', function () {
        return $this->circuits->count();
      }),
    ];
  }
}

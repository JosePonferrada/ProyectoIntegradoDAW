<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StewardDecisionResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->decision_id,
      'race' => new RaceResource($this->whenLoaded('race')),
      'document_path' => $this->document_path,
      'upload_date' => $this->upload_date,
      'description' => $this->description,
    ];
  }
}

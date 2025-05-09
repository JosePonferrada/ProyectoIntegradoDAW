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
      'document_number' => $this->document_number,
      'race' => new RaceResource($this->whenLoaded('race')),
      'driver' => new DriverResource($this->whenLoaded('driver')),
      'team' => new ConstructorResource($this->whenLoaded('team')),
      'document_path' => $this->document_path,
      'upload_date' => $this->upload_date,
      'fact' => $this->fact,
      'session_type' => $this->session_type,
      'incident_time' => $this->incident_time,
      'infringement_article' => $this->infringement_article,
      'decision_type' => $this->decision_type,
      'penalty_detail' => $this->penalty_detail,
      'content' => $this->content,
      'stewards' => $this->stewards,
    ];
  }
}

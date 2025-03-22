<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StewardDecision extends Model
{
  public $timestamps = false;
  protected $primaryKey = 'decision_id';

  protected $fillable = [
    'race_id',
    'document_path',
    'upload_date',
    'description'
  ];

  protected $casts = [
    'upload_date' => 'datetime'
  ];

  public function race(): BelongsTo
  {
    return $this->belongsTo(Race::class, 'race_id');
  }
}

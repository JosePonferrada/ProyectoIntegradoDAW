<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualifyingResult extends Model
{
  protected $primaryKey = 'qualifying_id';

  protected $fillable = [
    'race_id',
    'driver_id',
    'constructor_id',
    'position',
    'q1_time',
    'q2_time',
    'q3_time'
  ];

  public function race(): BelongsTo
  {
    return $this->belongsTo(Race::class, 'race_id');
  }

  public function driver(): BelongsTo
  {
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function constructor(): BelongsTo
  {
    return $this->belongsTo(Constructor::class, 'constructor_id');
  }
}

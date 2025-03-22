<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
  protected $primaryKey = 'result_id';

  protected $fillable = [
    'race_id',
    'driver_id',
    'constructor_id',
    'grid_position',
    'position',
    'position_text',
    'position_order',
    'points',
    'laps',
    'race_time',
    'fastest_lap',
    'fastest_lap_time',
    'fastest_lap_speed',
    'status'
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

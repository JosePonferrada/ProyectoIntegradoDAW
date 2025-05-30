<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverStanding extends Model
{
  protected $fillable = [
    'season_id',
    'driver_id',
    'race_id',
    'points',
    'position',
    'wins'
  ];

  public function season(): BelongsTo
  {
    return $this->belongsTo(Season::class, 'season_id');
  }

  public function driver(): BelongsTo
  {
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function race(): BelongsTo
  {
    return $this->belongsTo(Race::class, 'race_id');
  }

  public function constructor(): BelongsTo
  {
    return $this->belongsTo(Constructor::class, 'constructor_id');
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverConstructorSeason extends Model
{
  protected $fillable = [
    'driver_id',
    'constructor_id',
    'season_id',
    'position_number'
  ];

  public function driver(): BelongsTo
  {
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function constructor(): BelongsTo
  {
    return $this->belongsTo(Constructor::class, 'constructor_id');
  }

  public function season(): BelongsTo
  {
    return $this->belongsTo(Season::class, 'season_id');
  }
}

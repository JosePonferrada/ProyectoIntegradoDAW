<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circuit extends Model
{
  protected $primaryKey = 'circuit_id';

  protected $fillable = [
    'name',
    'location',
    'country_id',
    'length',
    'lap_record',
    'lap_record_driver',
    'lap_record_year',
    'map_image',
    'layout_image',
    'description'
  ];

  public function country(): BelongsTo
  {
    return $this->belongsTo(Country::class, 'country_id');
  }

  public function races(): HasMany
  {
    return $this->hasMany(Race::class, 'circuit_id');
  }
}
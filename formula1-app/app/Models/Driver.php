<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
  protected $primaryKey = 'driver_id';

  protected $fillable = [
    'first_name',
    'last_name',
    'code',
    'number',
    'date_of_birth',
    'nationality',
    'active',
    'biography',
    'image',
    'debut_year',
    'championships',
    'wins',
    'podiums',
    'poles',
    'fastest_laps'
  ];

  protected $casts = [
    'date_of_birth' => 'date',
    'active' => 'boolean',
  ];

  public function country(): BelongsTo
  {
    return $this->belongsTo(Country::class, 'nationality');
  }

  public function constructors(): BelongsToMany
  {
    return $this->belongsToMany(
      Constructor::class,
      'driver_constructor_seasons',
      'driver_id',
      'constructor_id'
    )->withPivot('season_id', 'position_number');
  }

  public function seasons(): BelongsToMany
  {
    return $this->belongsToMany(
      Season::class,
      'driver_constructor_seasons',
      'driver_id',
      'season_id'
    );
  }

  public function championships(): HasMany
  {
    return $this->hasMany(Season::class, 'reigning_champion_driver');
  }

  public function raceResults(): HasMany
  {
    return $this->hasMany(RaceResult::class, 'driver_id');
  }

  public function qualifyingResults(): HasMany
  {
    return $this->hasMany(QualifyingResult::class, 'driver_id');
  }

  public function standings(): HasMany
  {
    return $this->hasMany(DriverStanding::class, 'driver_id');
  }

  public function getFullNameAttribute(): string
  {
    return "{$this->first_name} {$this->last_name}";
  }
}

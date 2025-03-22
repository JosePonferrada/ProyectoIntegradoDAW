<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Constructor extends Model
{
  protected $primaryKey = 'constructor_id';

  protected $fillable = [
    'name',
    'nationality',
    'logo',
    'base',
    'first_team_entry',
    'team_chief',
    'technical_chief',
    'chassis',
    'power_unit',
    'official_website'
  ];

  public function country(): BelongsTo
  {
    return $this->belongsTo(Country::class, 'nationality');
  }

  public function drivers(): BelongsToMany
  {
    return $this->belongsToMany(
      Driver::class,
      'driver_constructor_seasons',
      'constructor_id',
      'driver_id'
    )->withPivot('season_id', 'position_number');
  }

  public function seasons(): BelongsToMany
  {
    return $this->belongsToMany(
      Season::class,
      'driver_constructor_seasons',
      'constructor_id',
      'season_id'
    );
  }

  public function championships(): HasMany
  {
    return $this->hasMany(Season::class, 'reigning_champion_constructor');
  }

  public function raceResults(): HasMany
  {
    return $this->hasMany(RaceResult::class, 'constructor_id');
  }

  public function qualifyingResults(): HasMany
  {
    return $this->hasMany(QualifyingResult::class, 'constructor_id');
  }

  public function standings(): HasMany
  {
    return $this->hasMany(ConstructorStanding::class, 'constructor_id');
  }
}

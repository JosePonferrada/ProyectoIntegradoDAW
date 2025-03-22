<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
  protected $primaryKey = 'season_id';

  protected $fillable = [
    'year',
    'races_count',
    'start_date',
    'end_date',
    'reigning_champion_driver',
    'reigning_champion_constructor',
    'regulation_changes'
  ];

  protected $casts = [
    'year' => 'integer',
    'start_date' => 'date',
    'end_date' => 'date',
  ];

  public function championDriver(): BelongsTo
  {
    return $this->belongsTo(Driver::class, 'reigning_champion_driver');
  }

  public function championConstructor(): BelongsTo
  {
    return $this->belongsTo(Constructor::class, 'reigning_champion_constructor');
  }

  public function races(): HasMany
  {
    return $this->hasMany(Race::class, 'season_id');
  }

  public function drivers(): BelongsToMany
  {
    return $this->belongsToMany(
      Driver::class,
      'driver_constructor_seasons',
      'season_id',
      'driver_id'
    )->withPivot('constructor_id', 'position_number');
  }

  public function constructors(): BelongsToMany
  {
    return $this->belongsToMany(
      Constructor::class,
      'driver_constructor_seasons',
      'season_id',
      'constructor_id'
    );
  }

  public function driverStandings(): HasMany
  {
    return $this->hasMany(DriverStanding::class, 'season_id');
  }

  public function constructorStandings(): HasMany
  {
    return $this->hasMany(ConstructorStanding::class, 'season_id');
  }
}

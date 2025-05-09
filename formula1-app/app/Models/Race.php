<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
  protected $primaryKey = 'race_id';

  protected $fillable = [
    'name',
    'season_id',
    'circuit_id',
    'round',
    'race_date',
    'start_time',
    'distance',
    'scheduled_laps',
    'completed_laps',
    'race_duration',
    'status',
    'weather_conditions',
    'avg_temperature',
    'notes',
    // Campos de la migración
    'practice1_date',
    'practice1_time',
    'practice2_date',
    'practice2_time',
    'practice3_date',
    'practice3_time',
    'qualifying_date',
    'qualifying_time',
    'sprint_qualifying_date',
    'sprint_qualifying_time',
    'sprint_date',
    'sprint_time',
    'weekend_format',
  ];

  protected $casts = [
    'race_date' => 'date',
    'start_time' => 'datetime',
    'race_duration' => 'datetime',
    'practice1_date' => 'date',
    'practice1_time' => 'datetime',
    'practice2_date' => 'date',
    'practice2_time' => 'datetime',
    'practice3_date' => 'date',
    'practice3_time' => 'datetime',
    'qualifying_date' => 'date',
    'qualifying_time' => 'datetime',
    'sprint_qualifying_date' => 'date',
    'sprint_qualifying_time' => 'datetime',
    'sprint_date' => 'date',
    'sprint_time' => 'datetime',
  ];

  public function season(): BelongsTo
  {
    return $this->belongsTo(Season::class, 'season_id');
  }

  public function circuit(): BelongsTo
  {
    return $this->belongsTo(Circuit::class, 'circuit_id');
  }

  public function results(): HasMany
  {
    return $this->hasMany(RaceResult::class, 'race_id');
  }

  public function qualifyingResults(): HasMany
  {
    return $this->hasMany(QualifyingResult::class, 'race_id');
  }

  public function driverStandings(): HasMany
  {
    return $this->hasMany(DriverStanding::class, 'race_id');
  }

  public function constructorStandings(): HasMany
  {
    return $this->hasMany(ConstructorStanding::class, 'race_id');
  }

  public function stewardDecisions(): HasMany
  {
    return $this->hasMany(StewardDecision::class, 'race_id');
  }
}

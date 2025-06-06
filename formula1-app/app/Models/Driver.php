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

  // Used on standings page
  public function nationality(): BelongsTo
  {
    return $this->belongsTo(Country::class, 'nationality');
  }

  public function currentTeam() {
    $currentYear = date('Y');
  
    return $this->belongsToMany(Constructor::class, 'driver_constructor_seasons', 'driver_id', 'constructor_id')
                ->withPivot('position_number')
                ->join('seasons', 'driver_constructor_seasons.season_id', '=', 'seasons.season_id')
                ->where('seasons.year', $currentYear)
                ->limit(1);
  }

  // ===========================

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
    )->withPivot('constructor_id', 'position_number')
      ->withTimestamps();
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

  public function currentConstructor()
  {
    $currentYear = date('Y');
    
    return $this->belongsToMany(Constructor::class, 'driver_constructor_seasons', 'driver_id', 'constructor_id')
                ->withPivot('position_number')
                ->join('seasons', 'driver_constructor_seasons.season_id', '=', 'seasons.season_id')
                ->where('seasons.year', $currentYear)
                ->limit(1);
  }

  // Obtenemos los pilotos principales de la temporada elegida
  public static function getMainDriversForSeason(int $seasonId)
  {
      return self::whereHas('seasons', function ($query) use ($seasonId) {
          $query->where('seasons.season_id', $seasonId)
                ->whereIn('driver_constructor_seasons.position_number', [1, 2]);
      })
      ->with([
          'nationality',
          // Carga la relación 'constructors' y filtra por la seasonId actual
          // Asegúrate que la relación 'constructors' en este modelo Driver
          // está definida con ->withPivot('season_id', 'position_number')
          'constructors' => function ($query) use ($seasonId) {
              $query->wherePivot('season_id', $seasonId);
          }
          // Puedes mantener la relación 'seasons' si la necesitas para otra cosa,
          // pero para obtener el constructor de la temporada actual, 'constructors' es más directo.
          // 'seasons' => function ($query) use ($seasonId) {
          //     $query->where('seasons.season_id', $seasonId)
          //           ->whereIn('driver_constructor_seasons.position_number', [1, 2]);
          // }
      ])
      ->orderBy('last_name')
      ->orderBy('first_name')
      ->get();
  }
}

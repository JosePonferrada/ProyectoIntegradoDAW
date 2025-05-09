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

  public function nationality(): BelongsTo
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

  public function currentDrivers()
  {
    try {
        // Don't rely on finding current year, get the latest season ID from the existing pivot table
        $latestSeasonId = \DB::table('driver_constructor_seasons')
            ->select('season_id')
            ->orderBy('season_id', 'desc')
            ->first()
            ?->season_id;
            
        if (!$latestSeasonId) {
            \Log::info('No driver constructor seasons found');
            return $this->belongsToMany(Driver::class)->whereRaw('1=0');
        }
        
        \Log::info("Using season ID {$latestSeasonId} for constructor {$this->name}");
        
        $driverQuery = $this->belongsToMany(Driver::class, 'driver_constructor_seasons', 'constructor_id', 'driver_id')
            ->withPivot(['position_number'])
            ->where('driver_constructor_seasons.season_id', $latestSeasonId);
        
        return $driverQuery;
    } catch (\Exception $e) {
        \Log::error('Error in currentDrivers relation: ' . $e->getMessage());
        return $this->belongsToMany(Driver::class)->whereRaw('1=0');
    }
  }
}

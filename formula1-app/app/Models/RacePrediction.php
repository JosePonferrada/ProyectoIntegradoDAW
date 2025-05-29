<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RacePrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'race_id', 
        'dnf_count', 
        'fastest_lap_driver_id',
        'points',
        'is_locked'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function race()
    {
    return $this->belongsTo(Race::class, 'race_id', 'race_id');
    }

    public function positions()
    {
        return $this->hasMany(PredictionPosition::class);
    }

    public function fastestLapDriver()
    {
        return $this->belongsTo(Driver::class, 'fastest_lap_driver_id', 'driver_id');
    }
}

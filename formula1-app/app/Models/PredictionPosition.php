<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PredictionPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'race_prediction_id', 
        'driver_id', 
        'position'
    ];

    public function racePrediction()
    {
        return $this->belongsTo(RacePrediction::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }
}

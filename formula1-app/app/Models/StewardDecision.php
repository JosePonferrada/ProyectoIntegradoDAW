<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StewardDecision extends Model
{
  public $timestamps = false;
  protected $primaryKey = 'decision_id';

    protected $fillable = [
        'race_id',
        'document_number',
        'driver_id',
        'team_id',
        'document_path',
        'fact',
        'session_type',
        'incident_time',
        'infringement_article',
        'decision_type',
        'penalty_detail',
        'content',
        'stewards',
        'upload_date'
    ];

    protected $casts = [
        'stewards' => 'array',
        'upload_date' => 'datetime'
    ];

    // Relaciones
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class, 'race_id', 'race_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Constructor::class, 'team_id', 'constructor_id');
    }
}

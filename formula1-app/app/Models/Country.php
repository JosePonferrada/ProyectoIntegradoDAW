<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
  public $timestamps = false;
  protected $primaryKey = 'country_id';

  protected $fillable = [
    'name',
    'code',
    'flag_img'
  ];

  public function circuits(): HasMany
  {
    return $this->hasMany(Circuit::class, 'country_id');
  }

  public function constructors(): HasMany
  {
    return $this->hasMany(Constructor::class, 'nationality');
  }

  public function drivers(): HasMany
  {
    return $this->hasMany(Driver::class, 'nationality');
  }
}

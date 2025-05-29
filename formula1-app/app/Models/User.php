<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;
// use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'name',
    'username',
    'email',
    'password',
    'role',
    'avatar',
    'country_id',
    'favorite_driver_id',
    'favorite_constructor_id',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function sendPasswordResetNotification($token)
  {
      $this->notify(new CustomResetPasswordNotification($token)); // ¡USA TU NOTIFICACIÓN AQUÍ!
  }
  public function sendEmailVerificationNotification()
  {
    $this->notify(new \App\Notifications\CustomVerifyEmail());
  }

  public function favoriteDriver()
  {
    return $this->belongsTo(\App\Models\Driver::class, 'favorite_driver_id');
  }

  public function favoriteConstructor()
  {
    return $this->belongsTo(\App\Models\Constructor::class, 'favorite_constructor_id');
  }

  public function country()
  {
    return $this->belongsTo(\App\Models\Country::class, 'country_id');
  }

  public function predictions()
  {
    return $this->hasMany(RacePrediction::class, 'user_id');
  }

  public function isAdmin()
  {
    return $this->role === 'admin';
  }
}

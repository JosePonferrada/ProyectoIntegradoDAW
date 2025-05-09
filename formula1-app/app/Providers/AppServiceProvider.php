<?php

namespace App\Providers;

use App\Models\Race;
use App\Models\RaceResult;
use App\Observers\RaceResultObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        \Illuminate\Support\Facades\Auth::macro('user', function () {
          $user = auth()->user();
          if ($user) {
              return $user->load('favoriteDriver', 'favoriteConstructor', 'country');
          }
          return null;
      });

      RaceResult::observe(RaceResultObserver::class);
    }
}

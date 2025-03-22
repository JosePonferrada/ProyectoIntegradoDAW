<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Controladores API
use App\Http\Controllers\API\CircuitController;
use App\Http\Controllers\API\ConstructorController;
use App\Http\Controllers\API\ConstructorStandingController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\DriverStandingController;
use App\Http\Controllers\API\QualifyingResultController;
use App\Http\Controllers\API\RaceController;
use App\Http\Controllers\API\RaceResultController;
use App\Http\Controllers\API\SeasonController;
use App\Http\Controllers\API\StewardDecisionController;

Route::get('/', function () {
  return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
  return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas API
Route::prefix('api')->group(function () {
  Route::apiResource('countries', CountryController::class);
  Route::apiResource('circuits', CircuitController::class);
  Route::apiResource('constructors', ConstructorController::class);
  Route::apiResource('seasons', SeasonController::class);
  Route::apiResource('drivers', DriverController::class);
  Route::apiResource('races', RaceController::class);
  Route::apiResource('qualifying-results', QualifyingResultController::class);
  Route::apiResource('race-results', RaceResultController::class);
  Route::apiResource('driver-standings', DriverStandingController::class);
  Route::apiResource('constructor-standings', ConstructorStandingController::class);
  Route::apiResource('steward-decisions', StewardDecisionController::class);
});

// Rutas de la aplicación
Route::middleware(['auth'])->group(function () {
  // Drivers routes
  Route::get('/drivers', function () {
    return Inertia::render('Formula1/Drivers');
  })->name('drivers');

  Route::get('/drivers/create', function () {
    $countries = \App\Models\Country::all();
    return Inertia::render('Formula1/CreateDriver', [
      'countries' => $countries
    ]);
  })->name('drivers.create');

  Route::get('/drivers/{driver}/edit', function (\App\Models\Driver $driver) {
    return Inertia::render('Formula1/EditDriver', [
      'driver' => $driver->load('nationality'),
      'countries' => \App\Models\Country::all()
    ]);
  })->name('drivers.edit');

  // Constructors routes
  Route::get('/constructors', function () {
    return Inertia::render('Formula1/Constructors');
  })->name('constructors');

  Route::get('/circuits', function () {
    return Inertia::render('Formula1/Circuits');
  })->name('circuits');

  Route::get('/races', function () {
    return Inertia::render('Formula1/Races');
  })->name('races');

  Route::get('/standings', function () {
    return Inertia::render('Formula1/Standings');
  })->name('standings');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

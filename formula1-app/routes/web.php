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
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Formula1\RaceController as WebRaceController;

Route::get('/', function () {
  return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
  Route::get('/profile', function () {
      return Inertia::render('profile/Index');
  })->name('profile');

  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
});

Route::middleware('guest')->group(function () {
  // Modificar esta ruta para incluir los datos
  Route::get('register', [RegisteredUserController::class, 'create'])
      ->name('register');

  Route::post('register', [RegisteredUserController::class, 'store']);

  Route::get('login', [AuthenticatedSessionController::class, 'create'])
      ->name('login');

  Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

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

  Route::get('/seasons/{seasonId}/driver-progression', [App\Http\Controllers\API\DriverStandingController::class, 'getDriverProgressionBySeason']);
  Route::get('/seasons/{seasonId}/constructor-progression', [App\Http\Controllers\API\ConstructorStandingController::class, 'getConstructorProgressionBySeason']);
  Route::get('/seasons/{seasonId}/races', [App\Http\Controllers\API\RaceController::class, 'getRacesBySeason']);
  Route::post('/races/{race}/update-standings', [DriverStandingController::class, 'updateStandingsAfterRace']);

  Route::get('/races/{id}', [RaceController::class, 'show']);
  Route::get('/race-results', [RaceResultController::class, 'index']);
  Route::get('/qualifying-results', [QualifyingResultController::class, 'index']);

  Route::get('races/{race}/steward-decisions', [StewardDecisionController::class, 'getByRace']);
  Route::post('steward-decisions', [StewardDecisionController::class, 'store'])->middleware('auth');
  Route::get('steward-decisions/{id}/download', [StewardDecisionController::class, 'download']);
  Route::post('steward-decisions/generate', [StewardDecisionController::class, 'generateFromTemplate'])->middleware('auth');
  Route::delete('steward-decisions/{id}', [StewardDecisionController::class, 'destroy']);
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
      'driver_id' => $driver->driver_id,
      'countries' => \App\Models\Country::all()
    ]);
  })->name('drivers.edit');

  // Constructors routes
  Route::get('/constructors', function () {
    return Inertia::render('Formula1/Constructors');
  })->name('constructors');

  Route::get('/constructors/create', function () {
    $countries = \App\Models\Country::all();
    return Inertia::render('Formula1/CreateConstructor', [
      'countries' => $countries
    ]);
  })->name('constructors.create')->middleware('admin');

  Route::get('/constructors/{constructor}/edit', function (\App\Models\Constructor $constructor) {
    return Inertia::render('Formula1/EditConstructor', [
      'constructor_id' => $constructor->constructor_id,
      'countries' => \App\Models\Country::all()
    ]);
  })->name('constructors.edit');

  Route::get('/circuits', function () {
    return Inertia::render('Formula1/Circuits');
  })->name('circuits');

  Route::get('/races', function () {
    return Inertia::render('Formula1/Races');
  })->name('races');

  Route::get('/standings', function () {
    return Inertia::render('Formula1/Standings');
  })->name('standings');

  Route::get('/races/{id}', [WebRaceController::class, 'show'])->name('races.show');
  Route::get('/formula1/races/create', function () {
    return Inertia::render('Formula1/RaceCreate');
})->name('formula1.races.create');

Route::get('/formula1/races/{id}/edit', function ($id) {
    return Inertia::render('Formula1/RaceEdit', [
        'id' => $id
    ]);
})->name('formula1.races.edit');
});

Route::get('/debug-images', function() {
  $drivers = \App\Models\Driver::all(['driver_id', 'first_name', 'last_name', 'image']);
  foreach ($drivers as $driver) {
      echo "ID: {$driver->driver_id}, Nombre: {$driver->first_name} {$driver->last_name}, Imagen: {$driver->image}<br>";
      
      // Verificar si el archivo existe físicamente
      if ($driver->image) {
          $fullPath = public_path("storage/drivers/{$driver->image}");
          echo "¿Existe? " . (file_exists($fullPath) ? "SÍ" : "NO") . " - Ruta: {$fullPath}<br>";
          
          $alternativePath = public_path("storage/{$driver->image}");
          echo "¿Existe alternativa? " . (file_exists($alternativePath) ? "SÍ" : "NO") . " - Ruta: {$alternativePath}<br>";
      }
      echo "<hr>";
  }
});

Route::get('/races/{race}/calendar.ics', [App\Http\Controllers\CalendarController::class, 'downloadIcs'])
    ->name('races.calendar.ics');

Route::fallback(function () {
  return Inertia::render('Errors/404');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

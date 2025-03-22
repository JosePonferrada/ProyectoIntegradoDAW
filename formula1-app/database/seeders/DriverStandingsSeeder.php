<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\Race;
use App\Models\Driver;
use App\Models\DriverStanding;

class DriverStandingsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $season2023 = Season::where('year', 2023)->first();
    $bahrain = Race::where('name', 'Gran Premio de Bahréin')->first();

    if (!$season2023 || !$bahrain)
      return;

    $drivers = Driver::all()->keyBy('last_name');

    // Clasificación de pilotos después del GP de Bahréin 2023
    $standingsData = [
      ['driver' => 'Verstappen', 'points' => 25, 'position' => 1, 'wins' => 1],
      ['driver' => 'Pérez', 'points' => 18, 'position' => 2, 'wins' => 0],
      ['driver' => 'Alonso', 'points' => 15, 'position' => 3, 'wins' => 0],
      ['driver' => 'Sainz', 'points' => 12, 'position' => 4, 'wins' => 0],
      ['driver' => 'Hamilton', 'points' => 10, 'position' => 5, 'wins' => 0],
      ['driver' => 'Norris', 'points' => 0, 'position' => 16, 'wins' => 0],
      ['driver' => 'Leclerc', 'points' => 0, 'position' => 20, 'wins' => 0],
    ];

    foreach ($standingsData as $data) {
      $driver = $drivers->get($data['driver']);

      if ($driver) {
        DriverStanding::create([
          'season_id' => $season2023->season_id,
          'driver_id' => $driver->driver_id,
          'race_id' => $bahrain->race_id,
          'points' => $data['points'],
          'position' => $data['position'],
          'wins' => $data['wins'],
        ]);
      }
    }
  }
}

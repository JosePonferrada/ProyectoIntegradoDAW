<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Constructor;
use App\Models\Season;
use App\Models\DriverConstructorSeason;

class DriverConstructorSeasonsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Temp arrays to help with data creation
    $drivers = Driver::all()->keyBy('last_name');
    $constructors = Constructor::all()->keyBy('name');
    $seasons = Season::all()->keyBy('year');

    // 2023 Season Driver-Constructor pairings
    $pairings2023 = [
      ['driver' => 'Hamilton', 'constructor' => 'Mercedes-AMG Petronas F1 Team', 'position' => 1],
      ['driver' => 'Russell', 'constructor' => 'Mercedes-AMG Petronas F1 Team', 'position' => 2],
      ['driver' => 'Verstappen', 'constructor' => 'Red Bull Racing', 'position' => 1],
      ['driver' => 'Pérez', 'constructor' => 'Red Bull Racing', 'position' => 2],
      ['driver' => 'Leclerc', 'constructor' => 'Scuderia Ferrari', 'position' => 1],
      ['driver' => 'Sainz', 'constructor' => 'Scuderia Ferrari', 'position' => 2],
      ['driver' => 'Norris', 'constructor' => 'McLaren F1 Team', 'position' => 1],
      ['driver' => 'Piastri', 'constructor' => 'McLaren F1 Team', 'position' => 2],
      ['driver' => 'Alonso', 'constructor' => 'Aston Martin Aramco Cognizant F1 Team', 'position' => 1],
      ['driver' => 'Gasly', 'constructor' => 'Alpine F1 Team', 'position' => 1],
    ];

    // Get the 2023 season ID
    $season2023 = $seasons->get(2023);

    if ($season2023) {
      foreach ($pairings2023 as $pairing) {
        $driver = $drivers->get($pairing['driver']);
        $constructor = $constructors->get($pairing['constructor']);

        if ($driver && $constructor) {
          DriverConstructorSeason::create([
            'driver_id' => $driver->driver_id,
            'constructor_id' => $constructor->constructor_id,
            'season_id' => $season2023->season_id,
            'position_number' => $pairing['position']
          ]);
        }
      }
    }
  }
}

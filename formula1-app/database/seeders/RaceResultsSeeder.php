<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Constructor;
use App\Models\Race;
use App\Models\RaceResult;

class RaceResultsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Obtener la primera carrera de 2023 (Gran Premio de Bahréin)
    $race = Race::where('name', 'Gran Premio de Bahréin')->first();
    if (!$race)
      return;

    $drivers = Driver::all()->keyBy('last_name');
    $constructors = Constructor::all()->keyBy('name');

    // Datos simplificados de resultados para el GP de Bahréin 2023
    $resultsData = [
      [
        'driver' => 'Verstappen',
        'constructor' => 'Red Bull Racing',
        'grid_position' => 1,
        'position' => 1,
        'position_text' => '1',
        'position_order' => 1,
        'points' => 25,
        'laps' => 57,
        'fastest_lap' => 44,
        'fastest_lap_time' => '01:33.996',
        'status' => 'Finished'
      ],
      [
        'driver' => 'Pérez',
        'constructor' => 'Red Bull Racing',
        'grid_position' => 2,
        'position' => 2,
        'position_text' => '2',
        'position_order' => 2,
        'points' => 18,
        'laps' => 57,
        'fastest_lap' => 47,
        'fastest_lap_time' => '01:34.245',
        'status' => 'Finished'
      ],
      [
        'driver' => 'Alonso',
        'constructor' => 'Aston Martin Aramco Cognizant F1 Team',
        'grid_position' => 5,
        'position' => 3,
        'position_text' => '3',
        'position_order' => 3,
        'points' => 15,
        'laps' => 57,
        'fastest_lap' => 46,
        'fastest_lap_time' => '01:34.543',
        'status' => 'Finished'
      ],
      [
        'driver' => 'Sainz',
        'constructor' => 'Scuderia Ferrari',
        'grid_position' => 4,
        'position' => 4,
        'position_text' => '4',
        'position_order' => 4,
        'points' => 12,
        'laps' => 57,
        'fastest_lap' => 52,
        'fastest_lap_time' => '01:34.667',
        'status' => 'Finished'
      ],
      [
        'driver' => 'Hamilton',
        'constructor' => 'Mercedes-AMG Petronas F1 Team',
        'grid_position' => 7,
        'position' => 5,
        'position_text' => '5',
        'position_order' => 5,
        'points' => 10,
        'laps' => 57,
        'fastest_lap' => 50,
        'fastest_lap_time' => '01:34.889',
        'status' => 'Finished'
      ],
      [
        'driver' => 'Norris',
        'constructor' => 'McLaren F1 Team',
        'grid_position' => 8,
        'position' => 17,
        'position_text' => '17',
        'position_order' => 17,
        'points' => 0,
        'laps' => 55,
        'fastest_lap' => 40,
        'fastest_lap_time' => '01:35.446',
        'status' => 'Power Unit'
      ],
      [
        'driver' => 'Leclerc',
        'constructor' => 'Scuderia Ferrari',
        'grid_position' => 3,
        'position' => null,
        'position_text' => 'DNF',
        'position_order' => 20,
        'points' => 0,
        'laps' => 39,
        'fastest_lap' => 37,
        'fastest_lap_time' => '01:34.256',
        'status' => 'DNF'
      ],
    ];

    foreach ($resultsData as $data) {
      $driver = $drivers->get($data['driver']);
      $constructor = $constructors->get($data['constructor']);

      if ($driver && $constructor) {
        RaceResult::create([
          'race_id' => $race->race_id,
          'driver_id' => $driver->driver_id,
          'constructor_id' => $constructor->constructor_id,
          'grid_position' => $data['grid_position'],
          'position' => $data['position'],
          'position_text' => $data['position_text'],
          'position_order' => $data['position_order'],
          'points' => $data['points'],
          'laps' => $data['laps'],
          'fastest_lap' => $data['fastest_lap'],
          'fastest_lap_time' => $data['fastest_lap_time'],
          'status' => $data['status'],
        ]);
      }
    }
  }
}

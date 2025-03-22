<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\Circuit;
use App\Models\Race;

class RacesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $season2023 = Season::where('year', 2023)->first();
    $circuits = Circuit::all()->keyBy('name');

    if (!$season2023 || $circuits->isEmpty()) {
      return;
    }

    $races2023 = [
      [
        'name' => 'Gran Premio de Bahréin',
        'circuit' => 'Circuito Internacional de Bahréin',
        'round' => 1,
        'race_date' => '2023-03-05',
        'start_time' => '16:00:00',
        'distance' => 308.238,
        'scheduled_laps' => 57,
        'completed_laps' => 57,
        'status' => 'Completed',
        'weather_conditions' => 'Despejado, seco',
        'avg_temperature' => 22.5,
      ],
      [
        'name' => 'Gran Premio de Arabia Saudita',
        'circuit' => 'Circuito Urbano de Jeddah',
        'round' => 2,
        'race_date' => '2023-03-19',
        'start_time' => '18:00:00',
        'distance' => 308.45,
        'scheduled_laps' => 50,
        'completed_laps' => 50,
        'status' => 'Completed',
        'weather_conditions' => 'Despejado, seco',
        'avg_temperature' => 27.2,
      ],
      [
        'name' => 'Gran Premio de Australia',
        'circuit' => 'Circuito de Albert Park',
        'round' => 3,
        'race_date' => '2023-04-02',
        'start_time' => '15:00:00',
        'distance' => 302.071,
        'scheduled_laps' => 58,
        'completed_laps' => 58,
        'status' => 'Completed',
        'weather_conditions' => 'Despejado, seco',
        'avg_temperature' => 18.7,
      ],
      [
        'name' => 'Gran Premio de Miami',
        'circuit' => 'Autódromo Internacional de Miami',
        'round' => 5,
        'race_date' => '2023-05-07',
        'start_time' => '15:30:00',
        'distance' => 308.326,
        'scheduled_laps' => 57,
        'completed_laps' => 57,
        'status' => 'Completed',
        'weather_conditions' => 'Parcialmente nublado, seco',
        'avg_temperature' => 29.8,
      ],
      [
        'name' => 'Gran Premio de España',
        'circuit' => 'Circuito de Barcelona-Cataluña',
        'round' => 7,
        'race_date' => '2023-06-04',
        'start_time' => '15:00:00',
        'distance' => 307.236,
        'scheduled_laps' => 66,
        'completed_laps' => 66,
        'status' => 'Completed',
        'weather_conditions' => 'Soleado, seco',
        'avg_temperature' => 25.4,
      ],
    ];

    foreach ($races2023 as $raceData) {
      $circuit = $circuits->get($raceData['circuit']);
      if ($circuit) {
        Race::create([
          'name' => $raceData['name'],
          'season_id' => $season2023->season_id,
          'circuit_id' => $circuit->circuit_id,
          'round' => $raceData['round'],
          'race_date' => $raceData['race_date'],
          'start_time' => $raceData['start_time'],
          'distance' => $raceData['distance'],
          'scheduled_laps' => $raceData['scheduled_laps'],
          'completed_laps' => $raceData['completed_laps'],
          'status' => $raceData['status'],
          'weather_conditions' => $raceData['weather_conditions'],
          'avg_temperature' => $raceData['avg_temperature'],
        ]);
      }
    }
  }
}

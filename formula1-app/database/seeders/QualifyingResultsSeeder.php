<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Constructor;
use App\Models\Race;
use App\Models\QualifyingResult;

class QualifyingResultsSeeder extends Seeder
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

    // Datos simplificados de clasificación para el GP de Bahréin 2023
    $qualifyingData = [
      ['driver' => 'Verstappen', 'constructor' => 'Red Bull Racing', 'position' => 1, 'q1' => '01:29.754', 'q2' => '01:29.345', 'q3' => '01:29.708'],
      ['driver' => 'Pérez', 'constructor' => 'Red Bull Racing', 'position' => 2, 'q1' => '01:30.131', 'q2' => '01:29.455', 'q3' => '01:29.846'],
      ['driver' => 'Leclerc', 'constructor' => 'Scuderia Ferrari', 'position' => 3, 'q1' => '01:30.282', 'q2' => '01:29.603', 'q3' => '01:30.000'],
      ['driver' => 'Sainz', 'constructor' => 'Scuderia Ferrari', 'position' => 4, 'q1' => '01:30.465', 'q2' => '01:29.728', 'q3' => '01:30.154'],
      ['driver' => 'Alonso', 'constructor' => 'Aston Martin Aramco Cognizant F1 Team', 'position' => 5, 'q1' => '01:30.615', 'q2' => '01:29.811', 'q3' => '01:30.336'],
      ['driver' => 'Russell', 'constructor' => 'Mercedes-AMG Petronas F1 Team', 'position' => 6, 'q1' => '01:30.757', 'q2' => '01:30.067', 'q3' => '01:30.340'],
      ['driver' => 'Hamilton', 'constructor' => 'Mercedes-AMG Petronas F1 Team', 'position' => 7, 'q1' => '01:30.889', 'q2' => '01:30.035', 'q3' => '01:30.384'],
      ['driver' => 'Norris', 'constructor' => 'McLaren F1 Team', 'position' => 8, 'q1' => '01:31.025', 'q2' => '01:30.151', 'q3' => '01:30.584'],
    ];

    foreach ($qualifyingData as $data) {
      $driver = $drivers->get($data['driver']);
      $constructor = $constructors->get($data['constructor']);

      if ($driver && $constructor) {
        QualifyingResult::create([
          'race_id' => $race->race_id,
          'driver_id' => $driver->driver_id,
          'constructor_id' => $constructor->constructor_id,
          'position' => $data['position'],
          'q1_time' => $data['q1'],
          'q2_time' => $data['q2'] ?? null,
          'q3_time' => $data['q3'] ?? null,
        ]);
      }
    }
  }
}

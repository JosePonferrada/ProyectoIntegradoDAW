<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\Race;
use App\Models\Constructor;
use App\Models\ConstructorStanding;

class ConstructorStandingsSeeder extends Seeder
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

    $constructors = Constructor::all()->keyBy('name');

    // Clasificación de constructores después del GP de Bahréin 2023
    $standingsData = [
      ['constructor' => 'Red Bull Racing', 'points' => 43, 'position' => 1, 'wins' => 1],
      ['constructor' => 'Aston Martin Aramco Cognizant F1 Team', 'points' => 15, 'position' => 2, 'wins' => 0],
      ['constructor' => 'Scuderia Ferrari', 'points' => 12, 'position' => 3, 'wins' => 0],
      ['constructor' => 'Mercedes-AMG Petronas F1 Team', 'points' => 10, 'position' => 4, 'wins' => 0],
      ['constructor' => 'McLaren F1 Team', 'points' => 0, 'position' => 8, 'wins' => 0],
    ];

    foreach ($standingsData as $data) {
      $constructor = $constructors->get($data['constructor']);

      if ($constructor) {
        ConstructorStanding::create([
          'season_id' => $season2023->season_id,
          'constructor_id' => $constructor->constructor_id,
          'race_id' => $bahrain->race_id,
          'points' => $data['points'],
          'position' => $data['position'],
          'wins' => $data['wins'],
        ]);
      }
    }
  }
}

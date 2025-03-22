<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    // User::factory()->create([
    //     'name' => 'Test User',
    //     'email' => 'test@example.com',
    // ]);

    $this->call([
      CountriesSeeder::class,
      CircuitsSeeder::class,
      ConstructorsSeeder::class,
      DriversSeeder::class,
      SeasonsSeeder::class,
      DriverConstructorSeasonsSeeder::class,
      RacesSeeder::class,
      QualifyingResultsSeeder::class,
      RaceResultsSeeder::class,
      DriverStandingsSeeder::class,
      ConstructorStandingsSeeder::class,
      StewardDecisionsSeeder::class,
    ]);
  }
}

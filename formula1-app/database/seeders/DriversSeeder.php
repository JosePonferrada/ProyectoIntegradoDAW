<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Country;

class DriversSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $countryMapping = Country::pluck('country_id', 'code')->toArray();

    $drivers = [
      [
        'first_name' => 'Lewis',
        'last_name' => 'Hamilton',
        'code' => 'HAM',
        'number' => 44,
        'date_of_birth' => '1985-01-07',
        'nationality' => $countryMapping['GB'],
        'active' => true,
        'biography' => 'Lewis Hamilton es un piloto británico de Fórmula 1. Ha ganado siete campeonatos mundiales, igualando el récord de Michael Schumacher.',
        'image' => 'drivers/hamilton.jpg',
        'debut_year' => 2007,
        'championships' => 7,
        'wins' => 103,
        'podiums' => 191,
        'poles' => 104,
        'fastest_laps' => 61
      ],
      [
        'first_name' => 'Max',
        'last_name' => 'Verstappen',
        'code' => 'VER',
        'number' => 1,
        'date_of_birth' => '1997-09-30',
        'nationality' => $countryMapping['NL'],
        'active' => true,
        'biography' => 'Max Verstappen es un piloto neerlandés de Fórmula 1. En 2021 se convirtió en el primer piloto neerlandés en ganar el Campeonato Mundial de F1.',
        'image' => 'drivers/verstappen.jpg',
        'debut_year' => 2015,
        'championships' => 2,
        'wins' => 35,
        'podiums' => 77,
        'poles' => 21,
        'fastest_laps' => 23
      ],
      [
        'first_name' => 'Charles',
        'last_name' => 'Leclerc',
        'code' => 'LEC',
        'number' => 16,
        'date_of_birth' => '1997-10-16',
        'nationality' => $countryMapping['MC'],
        'active' => true,
        'biography' => 'Charles Leclerc es un piloto monegasco de Fórmula 1 que compite para Ferrari desde 2019.',
        'image' => 'drivers/leclerc.jpg',
        'debut_year' => 2018,
        'championships' => 0,
        'wins' => 5,
        'podiums' => 24,
        'poles' => 18,
        'fastest_laps' => 6
      ],
      [
        'first_name' => 'Sergio',
        'last_name' => 'Pérez',
        'code' => 'PER',
        'number' => 11,
        'date_of_birth' => '1990-01-26',
        'nationality' => $countryMapping['MX'],
        'active' => true,
        'biography' => 'Sergio "Checo" Pérez es un piloto mexicano de Fórmula 1 que actualmente corre para Red Bull Racing.',
        'image' => 'drivers/perez.jpg',
        'debut_year' => 2011,
        'championships' => 0,
        'wins' => 5,
        'podiums' => 27,
        'poles' => 3,
        'fastest_laps' => 10
      ],
      [
        'first_name' => 'Fernando',
        'last_name' => 'Alonso',
        'code' => 'ALO',
        'number' => 14,
        'date_of_birth' => '1981-07-29',
        'nationality' => $countryMapping['ES'],
        'active' => true,
        'biography' => 'Fernando Alonso es un piloto español de Fórmula 1, dos veces campeón del mundo. Actualmente compite para Aston Martin.',
        'image' => 'drivers/alonso.jpg',
        'debut_year' => 2001,
        'championships' => 2,
        'wins' => 32,
        'podiums' => 104,
        'poles' => 22,
        'fastest_laps' => 23
      ],
      [
        'first_name' => 'Lando',
        'last_name' => 'Norris',
        'code' => 'NOR',
        'number' => 4,
        'date_of_birth' => '1999-11-13',
        'nationality' => $countryMapping['GB'],
        'active' => true,
        'biography' => 'Lando Norris es un piloto británico de Fórmula 1 que compite para McLaren desde 2019.',
        'image' => 'drivers/norris.jpg',
        'debut_year' => 2019,
        'championships' => 0,
        'wins' => 1,
        'podiums' => 11,
        'poles' => 1,
        'fastest_laps' => 5
      ],
      [
        'first_name' => 'Carlos',
        'last_name' => 'Sainz',
        'code' => 'SAI',
        'number' => 55,
        'date_of_birth' => '1994-09-01',
        'nationality' => $countryMapping['ES'],
        'active' => true,
        'biography' => 'Carlos Sainz es un piloto español de Fórmula 1 que actualmente corre para Ferrari.',
        'image' => 'drivers/sainz.jpg',
        'debut_year' => 2015,
        'championships' => 0,
        'wins' => 2,
        'podiums' => 15,
        'poles' => 3,
        'fastest_laps' => 2
      ],
      [
        'first_name' => 'George',
        'last_name' => 'Russell',
        'code' => 'RUS',
        'number' => 63,
        'date_of_birth' => '1998-02-15',
        'nationality' => $countryMapping['GB'],
        'active' => true,
        'biography' => 'George Russell es un piloto británico de Fórmula 1 que compite para Mercedes desde 2022.',
        'image' => 'drivers/russell.jpg',
        'debut_year' => 2019,
        'championships' => 0,
        'wins' => 1,
        'podiums' => 9,
        'poles' => 1,
        'fastest_laps' => 6
      ],
      [
        'first_name' => 'Oscar',
        'last_name' => 'Piastri',
        'code' => 'PIA',
        'number' => 81,
        'date_of_birth' => '2001-04-06',
        'nationality' => $countryMapping['AU'],
        'active' => true,
        'biography' => 'Oscar Piastri es un piloto australiano de Fórmula 1 que compite para McLaren desde 2023.',
        'image' => 'drivers/piastri.jpg',
        'debut_year' => 2023,
        'championships' => 0,
        'wins' => 0,
        'podiums' => 2,
        'poles' => 0,
        'fastest_laps' => 0
      ],
      [
        'first_name' => 'Pierre',
        'last_name' => 'Gasly',
        'code' => 'GAS',
        'number' => 10,
        'date_of_birth' => '1996-02-07',
        'nationality' => $countryMapping['FR'],
        'active' => true,
        'biography' => 'Pierre Gasly es un piloto francés de Fórmula 1 que actualmente corre para Alpine.',
        'image' => 'drivers/gasly.jpg',
        'debut_year' => 2017,
        'championships' => 0,
        'wins' => 1,
        'podiums' => 3,
        'poles' => 0,
        'fastest_laps' => 3
      ]
    ];

    foreach ($drivers as $driver) {
      Driver::create($driver);
    }
  }
}

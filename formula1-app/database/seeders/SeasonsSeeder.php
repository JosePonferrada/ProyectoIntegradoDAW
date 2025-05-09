<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\Driver;
use App\Models\Constructor;

class SeasonsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $driverMapping = Driver::pluck('driver_id', 'last_name')->toArray();
    $constructorMapping = Constructor::pluck('constructor_id', 'name')->toArray();

    $seasons = [
      [
        'year' => 2021,
        'races_count' => 22,
        'start_date' => '2021-03-28',
        'end_date' => '2021-12-12',
        'reigning_champion_driver' => $driverMapping['Verstappen'] ?? null,
        'reigning_champion_constructor' => $constructorMapping['Mercedes-AMG Petronas F1 Team'] ?? null,
        'regulation_changes' => 'Regulaciones de aerodinámica modificadas para reducir la carga aerodinámica.'
      ],
      [
        'year' => 2022,
        'races_count' => 22,
        'start_date' => '2022-03-20',
        'end_date' => '2022-11-20',
        'reigning_champion_driver' => $driverMapping['Verstappen'] ?? null,
        'reigning_champion_constructor' => $constructorMapping['Red Bull Racing'] ?? null,
        'regulation_changes' => 'Introducción de coches con efecto suelo, llantas de 18 pulgadas y combustible E10.'
      ],
      [
        'year' => 2023,
        'races_count' => 22,
        'start_date' => '2023-03-05',
        'end_date' => '2023-11-26',
        'reigning_champion_driver' => $driverMapping['Verstappen'] ?? null,
        'reigning_champion_constructor' => $constructorMapping['Red Bull Racing'] ?? null,
        'regulation_changes' => 'Ajustes al piso para reducir el porpoising y cambios en las normas de seguridad.'
      ],
      [
        'year' => 2024,
        'races_count' => 24,
        'start_date' => '2024-03-02',
        'end_date' => '2024-12-08',
        'reigning_champion_driver' => $driverMapping['Verstappen'] ?? null,
        'reigning_champion_constructor' => $constructorMapping['Red Bull Racing'] ?? null,
        'regulation_changes' => 'Ajustes menores a las regulaciones técnicas y formato de carrera sprint.'
      ],
      [
        'year' => 2025,
        'races_count' => 24,
        'start_date' => '2025-03-01',
        'end_date' => '2025-12-07',
        'reigning_champion_driver' => null,
        'reigning_champion_constructor' => null,
        'regulation_changes' => 'Nuevas regulaciones aerodinámicas para mejorar las carreras en circuitos urbanos y reducción del peso mínimo de los coches.'
      ]
    ];

    foreach ($seasons as $season) {
      Season::create($season);
    }
  }
}

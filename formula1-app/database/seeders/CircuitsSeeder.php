<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Circuit;
use App\Models\Country;

class CircuitsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $countryMapping = Country::pluck('country_id', 'code')->toArray();

    $circuits = [
      [
        'name' => 'Circuito de Albert Park',
        'location' => 'Melbourne',
        'country_id' => $countryMapping['AU'],
        'length' => 5.303,
        'lap_record' => '01:20.260',
        'lap_record_driver' => 'Charles Leclerc',
        'lap_record_year' => 2022,
        'map_image' => 'circuits/albert_park.png',
        'description' => 'El Circuito de Albert Park es un circuito urbano situado en Melbourne, Australia, que alberga el Gran Premio de Australia de Fórmula 1.'
      ],
      [
        'name' => 'Circuito Internacional de Bahréin',
        'location' => 'Sakhir',
        'country_id' => $countryMapping['BH'],
        'length' => 5.412,
        'lap_record' => '01:31.447',
        'lap_record_driver' => 'Pedro de la Rosa',
        'lap_record_year' => 2005,
        'map_image' => 'circuits/bahrain.png',
        'description' => 'El Circuito Internacional de Bahréin es un circuito de carreras ubicado en Sakhir, Bahréin.'
      ],
      [
        'name' => 'Circuito Internacional de Shanghái',
        'location' => 'Shanghái',
        'country_id' => $countryMapping['CN'],
        'length' => 5.451,
        'lap_record' => '01:32.238',
        'lap_record_driver' => 'Michael Schumacher',
        'lap_record_year' => 2004,
        'map_image' => 'circuits/shanghai.png',
        'description' => 'El Circuito Internacional de Shanghái es un circuito de carreras situado en Shanghái, China.'
      ],
      [
        'name' => 'Autódromo Enzo e Dino Ferrari',
        'location' => 'Imola',
        'country_id' => $countryMapping['IT'],
        'length' => 4.909,
        'lap_record' => '01:15.484',
        'lap_record_driver' => 'Lewis Hamilton',
        'lap_record_year' => 2020,
        'map_image' => 'circuits/imola.png',
        'description' => 'El Autódromo Enzo e Dino Ferrari, conocido como Imola, es un circuito de carreras ubicado en Italia.'
      ],
      [
        'name' => 'Autódromo Internacional de Miami',
        'location' => 'Miami',
        'country_id' => $countryMapping['US'],
        'length' => 5.412,
        'lap_record' => '01:29.708',
        'lap_record_driver' => 'Max Verstappen',
        'lap_record_year' => 2022,
        'map_image' => 'circuits/miami.png',
        'description' => 'El Autódromo Internacional de Miami es un circuito temporal ubicado en Miami Gardens, Florida.'
      ],
      [
        'name' => 'Circuito de Barcelona-Cataluña',
        'location' => 'Montmeló',
        'country_id' => $countryMapping['ES'],
        'length' => 4.675,
        'lap_record' => '01:18.149',
        'lap_record_driver' => 'Max Verstappen',
        'lap_record_year' => 2021,
        'map_image' => 'circuits/barcelona.png',
        'description' => 'El Circuit de Barcelona-Catalunya es un autódromo situado en Montmeló, España.'
      ],
      [
        'name' => 'Circuito de Mónaco',
        'location' => 'Montecarlo',
        'country_id' => $countryMapping['MC'],
        'length' => 3.337,
        'lap_record' => '01:12.909',
        'lap_record_driver' => 'Lewis Hamilton',
        'lap_record_year' => 2021,
        'map_image' => 'circuits/monaco.png',
        'description' => 'El Circuito de Mónaco es un circuito urbano situado en las calles de Montecarlo y La Condamine, Mónaco.'
      ],
      [
        'name' => 'Circuito Urbano de Bakú',
        'location' => 'Bakú',
        'country_id' => $countryMapping['AZ'],
        'length' => 6.003,
        'lap_record' => '01:43.009',
        'lap_record_driver' => 'Charles Leclerc',
        'lap_record_year' => 2019,
        'map_image' => 'circuits/baku.png',
        'description' => 'El Circuito Urbano de Bakú es un circuito temporal ubicado en Bakú, Azerbaiyán.'
      ],
      [
        'name' => 'Circuito Gilles Villeneuve',
        'location' => 'Montreal',
        'country_id' => $countryMapping['CA'],
        'length' => 4.361,
        'lap_record' => '01:13.078',
        'lap_record_driver' => 'Valtteri Bottas',
        'lap_record_year' => 2019,
        'map_image' => 'circuits/gilles_villeneuve.png',
        'description' => 'El Circuito Gilles Villeneuve es un circuito de carreras situado en Montreal, Canadá.'
      ],
      [
        'name' => 'Red Bull Ring',
        'location' => 'Spielberg',
        'country_id' => $countryMapping['AT'],
        'length' => 4.318,
        'lap_record' => '01:05.619',
        'lap_record_driver' => 'Carlos Sainz',
        'lap_record_year' => 2020,
        'map_image' => 'circuits/red_bull_ring.png',
        'description' => 'El Red Bull Ring es un circuito de carreras situado en Spielberg, Austria.'
      ]
    ];

    foreach ($circuits as $circuit) {
      Circuit::create($circuit);
    }
  }
}

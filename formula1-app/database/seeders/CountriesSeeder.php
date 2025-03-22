<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountriesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $countries = [
      ['name' => 'Reino Unido', 'code' => 'GB', 'flag_img' => 'flags/gb.png'],
      ['name' => 'Alemania', 'code' => 'DE', 'flag_img' => 'flags/de.png'],
      ['name' => 'Italia', 'code' => 'IT', 'flag_img' => 'flags/it.png'],
      ['name' => 'España', 'code' => 'ES', 'flag_img' => 'flags/es.png'],
      ['name' => 'Francia', 'code' => 'FR', 'flag_img' => 'flags/fr.png'],
      ['name' => 'Mónaco', 'code' => 'MC', 'flag_img' => 'flags/mc.png'],
      ['name' => 'Países Bajos', 'code' => 'NL', 'flag_img' => 'flags/nl.png'],
      ['name' => 'Australia', 'code' => 'AU', 'flag_img' => 'flags/au.png'],
      ['name' => 'Finlandia', 'code' => 'FI', 'flag_img' => 'flags/fi.png'],
      ['name' => 'Bélgica', 'code' => 'BE', 'flag_img' => 'flags/be.png'],
      ['name' => 'Canadá', 'code' => 'CA', 'flag_img' => 'flags/ca.png'],
      ['name' => 'México', 'code' => 'MX', 'flag_img' => 'flags/mx.png'],
      ['name' => 'Brasil', 'code' => 'BR', 'flag_img' => 'flags/br.png'],
      ['name' => 'Estados Unidos', 'code' => 'US', 'flag_img' => 'flags/us.png'],
      ['name' => 'Japón', 'code' => 'JP', 'flag_img' => 'flags/jp.png'],
      ['name' => 'China', 'code' => 'CN', 'flag_img' => 'flags/cn.png'],
      ['name' => 'Singapur', 'code' => 'SG', 'flag_img' => 'flags/sg.png'],
      ['name' => 'Bahréin', 'code' => 'BH', 'flag_img' => 'flags/bh.png'],
      ['name' => 'Abu Dhabi', 'code' => 'AE', 'flag_img' => 'flags/ae.png'],
      ['name' => 'Hungría', 'code' => 'HU', 'flag_img' => 'flags/hu.png'],
      ['name' => 'Arabia Saudita', 'code' => 'SA', 'flag_img' => 'flags/sa.png'],
      ['name' => 'Qatar', 'code' => 'QA', 'flag_img' => 'flags/qa.png'],
      ['name' => 'Austria', 'code' => 'AT', 'flag_img' => 'flags/at.png'],
      ['name' => 'Azerbaiyán', 'code' => 'AZ', 'flag_img' => 'flags/az.png']
    ];

    // For each country in the array, create a new record in the database
    foreach ($countries as $country) {
      Country::create($country);
    }
  }
}

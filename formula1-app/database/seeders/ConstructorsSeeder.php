<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Constructor;
use App\Models\Country;

class ConstructorsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $countryMapping = Country::pluck('country_id', 'code')->toArray();

    $constructors = [
      [
        'name' => 'Mercedes-AMG Petronas F1 Team',
        'nationality' => $countryMapping['DE'],
        'logo' => 'constructors/mercedes.png',
        'base' => 'Brackley, Reino Unido',
        'first_team_entry' => 1970,
        'team_chief' => 'Toto Wolff',
        'technical_chief' => 'James Allison',
        'chassis' => 'W14',
        'power_unit' => 'Mercedes',
        'official_website' => 'https://www.mercedesamgf1.com'
      ],
      [
        'name' => 'Red Bull Racing',
        'nationality' => $countryMapping['AT'],
        'logo' => 'constructors/red_bull.png',
        'base' => 'Milton Keynes, Reino Unido',
        'first_team_entry' => 2005,
        'team_chief' => 'Christian Horner',
        'technical_chief' => 'Pierre Waché',
        'chassis' => 'RB19',
        'power_unit' => 'Honda RBPT',
        'official_website' => 'https://www.redbullracing.com'
      ],
      [
        'name' => 'Scuderia Ferrari',
        'nationality' => $countryMapping['IT'],
        'logo' => 'constructors/ferrari.png',
        'base' => 'Maranello, Italia',
        'first_team_entry' => 1950,
        'team_chief' => 'Frédéric Vasseur',
        'technical_chief' => 'Enrico Cardile',
        'chassis' => 'SF-23',
        'power_unit' => 'Ferrari',
        'official_website' => 'https://www.ferrari.com/en-EN/formula1'
      ],
      [
        'name' => 'McLaren F1 Team',
        'nationality' => $countryMapping['GB'],
        'logo' => 'constructors/mclaren.png',
        'base' => 'Woking, Reino Unido',
        'first_team_entry' => 1966,
        'team_chief' => 'Andrea Stella',
        'technical_chief' => 'James Key',
        'chassis' => 'MCL60',
        'power_unit' => 'Mercedes',
        'official_website' => 'https://www.mclaren.com/racing'
      ],
      [
        'name' => 'Aston Martin Aramco Cognizant F1 Team',
        'nationality' => $countryMapping['GB'],
        'logo' => 'constructors/aston_martin.png',
        'base' => 'Silverstone, Reino Unido',
        'first_team_entry' => 2021,
        'team_chief' => 'Mike Krack',
        'technical_chief' => 'Andy Green',
        'chassis' => 'AMR23',
        'power_unit' => 'Mercedes',
        'official_website' => 'https://www.astonmartinf1.com'
      ],
      [
        'name' => 'Alpine F1 Team',
        'nationality' => $countryMapping['FR'],
        'logo' => 'constructors/alpine.png',
        'base' => 'Enstone, Reino Unido',
        'first_team_entry' => 2021,
        'team_chief' => 'Otmar Szafnauer',
        'technical_chief' => 'Pat Fry',
        'chassis' => 'A523',
        'power_unit' => 'Renault',
        'official_website' => 'https://www.alpinecars.com/en/formula-1'
      ],
      [
        'name' => 'Williams Racing',
        'nationality' => $countryMapping['GB'],
        'logo' => 'constructors/williams.png',
        'base' => 'Grove, Reino Unido',
        'first_team_entry' => 1978,
        'team_chief' => 'James Vowles',
        'technical_chief' => 'FX Demaison',
        'chassis' => 'FW45',
        'power_unit' => 'Mercedes',
        'official_website' => 'https://www.williamsf1.com'
      ],
      [
        'name' => 'MoneyGram Haas F1 Team',
        'nationality' => $countryMapping['US'],
        'logo' => 'constructors/haas.png',
        'base' => 'Kannapolis, Estados Unidos',
        'first_team_entry' => 2016,
        'team_chief' => 'Guenther Steiner',
        'technical_chief' => 'Simone Resta',
        'chassis' => 'VF-23',
        'power_unit' => 'Ferrari',
        'official_website' => 'https://www.haasf1team.com'
      ],
      [
        'name' => 'Alfa Romeo F1 Team Stake',
        'nationality' => $countryMapping['CH'] ?? $countryMapping['IT'],
        'logo' => 'constructors/alfa_romeo.png',
        'base' => 'Hinwil, Suiza',
        'first_team_entry' => 1993,
        'team_chief' => 'Alessandro Alunni Bravi',
        'technical_chief' => 'Jan Monchaux',
        'chassis' => 'C43',
        'power_unit' => 'Ferrari',
        'official_website' => 'https://www.sauber-group.com/motorsport/formula-1'
      ],
      [
        'name' => 'Scuderia AlphaTauri',
        'nationality' => $countryMapping['IT'],
        'logo' => 'constructors/alphatauri.png',
        'base' => 'Faenza, Italia',
        'first_team_entry' => 2020,
        'team_chief' => 'Franz Tost',
        'technical_chief' => 'Jody Egginton',
        'chassis' => 'AT04',
        'power_unit' => 'Honda RBPT',
        'official_website' => 'https://www.scuderiaalphatauri.com'
      ]
    ];

    foreach ($constructors as $constructor) {
      Constructor::create($constructor);
    }
  }
}

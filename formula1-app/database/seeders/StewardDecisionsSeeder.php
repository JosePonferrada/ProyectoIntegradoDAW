<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Race;
use App\Models\StewardDecision;

class StewardDecisionsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $bahrain = Race::where('name', 'Gran Premio de Bahréin')->first();

    if (!$bahrain)
      return;

    // Decisiones de los comisarios para el GP de Bahréin 2023
    $decisionsData = [
      [
        'document_path' => 'documents/bahrain/decision_01.pdf',
        'upload_date' => '2023-03-05 12:35:00',
        'description' => 'Decisión sobre incidente entre HAM y VER en curva 1 - Sin acción adicional'
      ],
      [
        'document_path' => 'documents/bahrain/decision_02.pdf',
        'upload_date' => '2023-03-05 14:20:00',
        'description' => 'Penalización de 5 segundos a LEC por exceder límites de pista'
      ],
      [
        'document_path' => 'documents/bahrain/decision_03.pdf',
        'upload_date' => '2023-03-05 18:45:00',
        'description' => 'Investigación sobre fallo técnico en el coche de Leclerc'
      ]
    ];

    foreach ($decisionsData as $data) {
      StewardDecision::create([
        'race_id' => $bahrain->race_id,
        'document_path' => $data['document_path'],
        'upload_date' => $data['upload_date'],
        'description' => $data['description'],
      ]);
    }
  }
}

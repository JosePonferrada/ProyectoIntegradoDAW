<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StewardDecisionResource;
use App\Models\StewardDecision;
use App\Models\Race;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Añadir este import
use PDF;

class StewardDecisionController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    $query = StewardDecision::query()->with('race');

    if ($request->has('race_id')) {
      $query->where('race_id', $request->race_id);
    }

    return StewardDecisionResource::collection($query->get());
  }

  public function store(Request $request): StewardDecisionResource
  {
    $request->validate([
      'document' => 'required|file|mimes:pdf|max:5120', // Max 5MB
      'fact' => 'required|string|max:255',
      'race_id' => 'required|exists:races,race_id'
    ]);

    // Store the file
    $path = $request->file('document')->store('documents/' . $request->race_id, 'public');

    // Create the record
    $decision = StewardDecision::create([
      'race_id' => $request->race_id,
      'document_path' => $path,
      'fact' => $request->fact,
      'upload_date' => now()
    ]);

    return new StewardDecisionResource($decision);
  }

  public function show(StewardDecision $stewardDecision): StewardDecisionResource
  {
    return new StewardDecisionResource($stewardDecision->load('race'));
  }

  public function update(Request $request, StewardDecision $stewardDecision): StewardDecisionResource
  {
    $validated = $request->validate([
      'race_id' => 'nullable|exists:races,race_id',
      'document_path' => 'sometimes|required|string|max:255',
      'upload_date' => 'nullable|date',
      'fact' => 'nullable|string',
    ]);

    $stewardDecision->update($validated);

    return new StewardDecisionResource($stewardDecision->load('race'));
  }

  /**
   * Delete a steward decision document
   */
  public function destroy($id)
  {
    $decision = StewardDecision::findOrFail($id);
    
    // Eliminar el archivo físico
    if (Storage::disk('public')->exists($decision->document_path)) {
        Storage::disk('public')->delete($decision->document_path);
    }
    
    // Eliminar el registro de la base de datos
    $decision->delete();
    
    return response()->json(['message' => 'Document deleted successfully'], 200);
  }

  /**
   * Get all steward decisions for a specific race
   */
  public function getByRace($raceId)
  {
    $decisions = StewardDecision::where('race_id', $raceId)
      ->orderBy('upload_date', 'desc')
      ->get();

    return StewardDecisionResource::collection($decisions);
  }

  /**
   * Download a steward decision document
   */
  public function download($id)
  {
    $decision = StewardDecision::findOrFail($id);

    if (!Storage::disk('public')->exists($decision->document_path)) {
      abort(404, 'Document not found');
    }

    return Storage::disk('public')->download(
      $decision->document_path,
      Str::slug($decision->fact) . '.pdf' // Cambiar str_slug a Str::slug
    );
  }

  /**
   * Generate a PDF from template
   */
  public function generateFromTemplate(Request $request)
  {
    $request->validate([
        'race_id' => 'required|exists:races,race_id',
        'template_type' => 'required|string|in:penalty,reprimand,fine,disqualification,investigation,warning',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'driver_id' => 'nullable|exists:drivers,driver_id',
        'team_id' => 'nullable|exists:constructors,constructor_id',
        'session_type' => 'nullable|string',
        'incident_time' => 'nullable|string',
        'infringement_article' => 'nullable|string',
        'penalty_detail' => 'nullable|string',
        'fine_amount' => 'nullable|numeric|min:0', // Nueva validación para fine_amount
    ]);
    
    // Get related data
    $race = Race::with('circuit', 'season')->where('race_id', $request->race_id)->firstOrFail();
    $driver = null;
    $team = null;
    
    if ($request->driver_id) {
        $driver = \App\Models\Driver::where('driver_id', $request->driver_id)->firstOrFail();
    }
    
    if ($request->team_id) {
        $team = \App\Models\Constructor::where('constructor_id', $request->team_id)->firstOrFail();
    }
    
    // Obtener el último número de documento para esta carrera
    $lastDocNumber = StewardDecision::where('race_id', $request->race_id)
        ->max('document_number') ?? 0;
    $documentNumber = $lastDocNumber + 1;
    
    // Primero creamos el registro en la base de datos
    $filename = 'documents/' . $request->race_id . '/doc-' . $documentNumber . '-' . Str::slug($request->title) . '.pdf';
    
    $decision = StewardDecision::create([
        'race_id' => $request->race_id,
        'document_number' => $documentNumber,
        'driver_id' => $request->driver_id,
        'team_id' => $request->team_id,
        'document_path' => $filename,
        'fact' => $request->title,
        'session_type' => $request->session_type ?? 'Race',
        'incident_time' => $request->incident_time ?? now()->format('H:i'),
        'infringement_article' => $request->infringement_article,
        'decision_type' => $request->template_type,
        'penalty_detail' => $request->penalty_detail,
        'content' => $request->content,
        'stewards' => [
            'FIA Steward', 'FIA Steward', 'The Stewards', 'FIA Steward'
        ],
        'upload_date' => now()
    ]);
    
    // Asegurarse de que el directorio existe
    Storage::disk('public')->makeDirectory(dirname($filename));
    
    // Generar el PDF
    $pdf = PDF::loadView('pdf.steward-decision', [
        'race' => $race,
        'type' => $request->template_type,
        'title' => $request->title,
        'content' => $request->content,
        'driver' => $driver,
        'team' => $team,
        'session_type' => $request->session_type ?? 'Race',
        'incident_time' => $request->incident_time ?? now()->format('H:i'),
        'infringement_article' => $request->infringement_article,
        'penalty_detail' => $request->penalty_detail,
        'fine_amount' => $request->fine_amount ? '€' . number_format($request->fine_amount) : '€0', // Formatear la cantidad
        'documentId' => $documentNumber
    ]);
    
    // Guardar el PDF
    Storage::disk('public')->put($filename, $pdf->output());
    
    return new StewardDecisionResource($decision);
  }
}

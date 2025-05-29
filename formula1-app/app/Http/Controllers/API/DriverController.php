<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return DriverResource::collection(
      Driver::with(['country', 'seasons'])->orderBy('active', 'desc')->orderBy('last_name')->get()
    );
  }

  public function store(Request $request)
  {
    try {
        \Log::debug('Raw request data:', $request->all());

        // Validación
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'code' => 'required|string|max:3',
            'number' => 'required|integer|min:0|max:99',
            'nationality' => 'required|integer|exists:countries,country_id',
            'date_of_birth' => 'required|date',
            'debut_year' => 'required|integer|min:1950|max:' . date('Y'),
            'championships' => 'integer|min:0',
            'wins' => 'integer|min:0',
            'podiums' => 'integer|min:0',
            'poles' => 'integer|min:0',
            'active' => 'required|boolean',
            'biography' => 'nullable|string',
            'image' => 'nullable|string',
            'image_file' => 'nullable|file|image|max:10240',
            'constructor_id' => 'nullable|exists:constructors,constructor_id',
            'season_id' => 'nullable|exists:seasons,season_id',
            'position_number' => 'nullable|integer|min:1|max:4',
        ]);

        // Crear piloto sin imagen
        $driver = Driver::create(collect($validated)->except('image_file')->toArray());

        // Procesar imagen simplificada (como en el perfil)
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('drivers', 'public');
            $driver->image = $path; // Guarda la ruta completa
            $driver->save();
        }

        // Guardar la relación con el constructor si se proporciona
        if ($request->filled('constructor_id') && $request->filled('season_id')) {
            // Insertar relación en la tabla pivote
            DB::table('driver_constructor_seasons')->insert([
                'driver_id' => $driver->driver_id,
                'constructor_id' => $request->constructor_id,
                'season_id' => $request->season_id,
                'position_number' => $request->position_number ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Agregar log para verificar
            \Log::info("Created driver-constructor-season relationship", [
                'driver_id' => $driver->driver_id,
                'constructor_id' => $request->constructor_id,
                'season_id' => $request->season_id,
                'position' => $request->position_number
            ]);
        }
        
        \Log::debug('Driver created successfully:', $driver->toArray());

        return response()->json([
            'message' => 'Driver created successfully',
            'data' => $driver
        ], 201);
        
    } catch (\Exception $e) {
        \Log::error('Error creating driver:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'Error creating driver',
            'error' => $e->getMessage()
        ], 500);
    }
  }

  public function show(Driver $driver): DriverResource
  {
    // Explicitly load pivot data with the seasons relationship
    $driver->load([
      'country',
      'constructors',
      'championships',
      'seasons' => function($query) {
        // This explicitly tells Laravel to include pivot columns in the response
        $query->withPivot('constructor_id', 'position_number');
      },
    ]);

    // Add before returning the resource
    \Log::info('Driver seasons data for driver ID ' . $driver->driver_id, [
        'seasons_count' => $driver->seasons->count(),
        'has_pivot_data' => $driver->seasons->isNotEmpty() && isset($driver->seasons->first()->pivot),
        'sample_pivot' => $driver->seasons->first() ? [
            'season_id' => $driver->seasons->first()->id,
            'constructor_id' => $driver->seasons->first()->pivot->constructor_id ?? 'missing',
            'position_number' => $driver->seasons->first()->pivot->position_number ?? 'missing'
        ] : 'No seasons'
    ]);

    // Computed properties
    $driver->points = $driver->raceResults->sum('points');

    return new DriverResource($driver);
  }

  public function update(Request $request, Driver $driver)
  {
    try {
        // Depuración de datos recibidos
        \Log::info('Datos recibidos:', [
            'all_data' => $request->all(),
            'has_first_name' => $request->has('first_name'),
            'first_name' => $request->input('first_name'),
            'has_image_file' => $request->hasFile('image_file'),
            'remove_image' => $request->boolean('remove_image'),
        ]);

        // Validación con manejo explícito de errores
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'code' => 'required|string|max:3',
            'number' => 'required|integer|min:0|max:99',
            'nationality' => 'required|integer|exists:countries,country_id',
            'date_of_birth' => 'required|date',
            'active' => 'boolean',
            'biography' => 'nullable|string',
            'debut_year' => 'required|integer|min:1950|max:' . date('Y'),
            'championships' => 'integer|min:0',
            'wins' => 'integer|min:0',
            'podiums' => 'integer|min:0',
            'poles' => 'integer|min:0',
            'fastest_laps' => 'integer|min:0',
            'image_file' => 'nullable|file|image|max:10240',
        ]);

        if ($validator->fails()) {
            \Log::warning('Errores de validación:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Resto del código de actualización...
        \Log::info('Iniciando actualización de piloto', [
            'driver_id' => $driver->driver_id,
            'has_image' => $request->hasFile('image_file'),
            'remove_image' => $request->boolean('remove_image', false),
            'current_image' => $driver->image
        ]);

        // Actualizar campos básicos del piloto
        $driver->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'code' => $validated['code'],
            'number' => $validated['number'],
            'nationality' => $validated['nationality'],
            'date_of_birth' => $validated['date_of_birth'],
            'active' => $validated['active'] ?? false,
            'biography' => $validated['biography'] ?? null,
            'debut_year' => $validated['debut_year'],
            'championships' => $validated['championships'] ?? 0,
            'wins' => $validated['wins'] ?? 0,
            'podiums' => $validated['podiums'] ?? 0,
            'poles' => $validated['poles'] ?? 0,
            'fastest_laps' => $validated['fastest_laps'] ?? 0,
        ]);

        // Guardar la imagen actual antes de modificar
        $oldImage = $driver->image;
        
        // 1. Si hay una nueva imagen
        if ($request->hasFile('image_file')) {
            \Log::info('Procesando nueva imagen', ['file' => $request->file('image_file')->getClientOriginalName()]);
            
            // Guardar nueva imagen con un nombre único
            $fileName = 'driver_' . time() . '_' . $driver->driver_id . '.' . $request->file('image_file')->getClientOriginalExtension();
            $path = $request->file('image_file')->storeAs('drivers', $fileName, 'public');
            
            // Actualizar modelo con nueva ruta
            $driver->image = $path;
            \Log::info('Nueva imagen guardada', ['nueva_ruta' => $path]);
            
            // Eliminar imagen anterior (después de asignar la nueva)
            if ($oldImage) {
                $this->deleteImageFile($oldImage);
            }
        }
        // 2. Si se solicita eliminar la imagen sin reemplazo
        else if ($request->boolean('remove_image', false)) {
            \Log::info('Solicitando eliminar imagen sin reemplazo');
            
            // Actualizar modelo para eliminar referencia a imagen
            $driver->image = null;
            
            // Eliminar archivo físico
            if ($oldImage) {
                $this->deleteImageFile($oldImage);
            }
        }

        // Guardar en la base de datos SIN transacción para depurar
        $saveResult = $driver->save();
        \Log::info('Resultado de save():', ['result' => $saveResult]);
        
        // Forzar actualización directa en la BD para asegurar
        \DB::table('drivers')
           ->where('driver_id', $driver->driver_id)
           ->update([
               'first_name' => $driver->first_name,
               'last_name' => $driver->last_name,
               'code' => $driver->code,
               'number' => $driver->number,
               'nationality' => $driver->nationality,
               'date_of_birth' => $driver->date_of_birth,
               'active' => $driver->active ? 1 : 0,
               'biography' => $driver->biography,
               'image' => $driver->image,
               'debut_year' => $driver->debut_year,
               'championships' => $driver->championships,
               'wins' => $driver->wins,
               'podiums' => $driver->podiums,
               'poles' => $driver->poles,
               'fastest_laps' => $driver->fastest_laps
           ]);
        
        // Verificación post-guardado
        $updatedDriver = Driver::find($driver->driver_id);
        \Log::info('Verificación después de guardar:', [
            'driver_id' => $updatedDriver->driver_id,
            'imagen_vieja' => $oldImage,
            'imagen_nueva' => $updatedDriver->image
        ]);

        // Process the season entries
        if ($request->has('season_entries')) {
            $seasonEntries = json_decode($request->season_entries, true);
            
            // Prepare the data for sync
            $pivotData = [];
            foreach ($seasonEntries as $entry) {
                if (!empty($entry['season_id'])) {
                    $pivotData[$entry['season_id']] = [
                        'constructor_id' => !empty($entry['constructor_id']) ? $entry['constructor_id'] : null,
                        'position_number' => !empty($entry['position_number']) ? $entry['position_number'] : null
                    ];
                }
            }
            
            // Sync with the pivot table
            $driver->seasons()->sync($pivotData);
            
            // For debugging
            \Log::info("Season entries synced for driver {$driver->driver_id}", [
                'count' => count($pivotData),
                'data' => $pivotData
            ]);
        }
        
        return new DriverResource($updatedDriver);
    } catch (\Exception $e) {
        \Log::error('Error actualizando piloto: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'message' => 'Error updating driver',
            'error' => $e->getMessage()
        ], 500);
    }
  }

  public function destroy(Driver $driver): Response
  {
    $driver->delete();

    return response()->noContent();
  }

  private function normalizeString(string $string): string
  {
    return preg_replace('/[^a-zA-Z0-9]/', '', strtolower($string));
  }

  // Método auxiliar para procesar imágenes
  private function processDriverImage($driver, $imageFile, $lastName = null)
  {
      // Usar apellido proporcionado o el existente
      $lastName = $lastName ?? $driver->last_name;
      $lastName = Str::slug($lastName);
      
      // Eliminar imagen anterior si existe
      if ($driver->image) {
          \Illuminate\Support\Facades\Storage::disk('public')->delete($driver->image);
      }
      
      // Generar nombre de archivo
      $fileName = $driver->driver_id . '_' . $lastName . '.' . $imageFile->getClientOriginalExtension();
      
      // Guardar archivo
      $imageFile->storeAs('public/drivers', $fileName);
      
      // Devolver ruta para la BD
      return 'drivers/' . $fileName;
  }

  // Método auxiliar para eliminar imágenes
  private function deleteImageFile($imagePath)
  {
    $fullPath = storage_path('app/public/' . $imagePath);
    \Log::info('Intentando eliminar archivo', [
      'path' => $imagePath,
      'fullPath' => $fullPath,
      'exists' => file_exists($fullPath)
    ]);
    
    if (file_exists($fullPath)) {
      try {
        unlink($fullPath);
        \Log::info('Archivo eliminado exitosamente');
        return true;
      } catch (\Exception $e) {
        \Log::error('Error eliminando archivo: ' . $e->getMessage(), [
          'path' => $fullPath
        ]);
        return false;
      }
    } else {
      \Log::warning('El archivo a eliminar no existe', [
        'path' => $fullPath
      ]);
      return false;
    }
  }

  public function getMainDriversForSeason($seasonId) {
    $drivers = Driver::getMainDriversForSeason($seasonId);
    return DriverResource::collection($drivers);
  }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstructorResource;
use App\Models\Constructor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConstructorController extends Controller
{
  public function index(Request $request): AnonymousResourceCollection
  {
    try {
        // Get season filter from request
        $seasonYear = $request->query('year');
        
        // Debug for requested year
        Log::info("Constructor API: Year requested: " . ($seasonYear ?: 'none (using latest)'));
        
        // Get season ID based on year
        $seasonId = null;
        if ($seasonYear) {
            // Use season_id column name, not id!
            $season = DB::table('seasons')
                ->where('year', $seasonYear)
                ->first();
                
            if ($season) {
                $seasonId = $season->season_id;
                Log::info("Found season ID {$seasonId} for year {$seasonYear}");
            } else {
                Log::warning("No season found for year {$seasonYear}");
            }
        } else {
            // Get most recent season
            $latestSeason = DB::table('seasons')
                ->orderBy('year', 'desc')
                ->first();
                
            if ($latestSeason) {
                $seasonId = $latestSeason->season_id;
                $seasonYear = $latestSeason->year;
                Log::info("Using latest season ID {$seasonId} (year {$seasonYear})");
            } else {
                Log::warning("No seasons found in database");
            }
        }
        
        // Debug for available seasons
        $allSeasons = DB::table('seasons')->orderBy('year', 'desc')->get();
        Log::info("Available seasons: " . $allSeasons->pluck('year')->implode(', '));
        
        // Load constructors with their basic info
        $constructors = Constructor::with(['country'])->get();
        Log::info("Found " . $constructors->count() . " constructors");
        
        // Process each constructor
        foreach ($constructors as $constructor) {
            try {
                // Check if this constructor has ANY drivers in ANY season
                $hasAnyDrivers = DB::table('driver_constructor_seasons')
                    ->where('constructor_id', $constructor->constructor_id)
                    ->exists();
                    
                Log::info("Constructor {$constructor->name} has any drivers in any season: " . 
                    ($hasAnyDrivers ? 'YES' : 'NO'));
                
                // If we have a valid season_id, try to get drivers for that season
                if ($seasonId) {
                    // Debug constructor-specific driver data
                    $driversForThisSeason = DB::table('driver_constructor_seasons')
                        ->where('constructor_id', $constructor->constructor_id)
                        ->where('season_id', $seasonId)
                        ->get();
                        
                    Log::info("Constructor {$constructor->name} has " . 
                        $driversForThisSeason->count() . " drivers in season {$seasonYear}");
                    
                    // If no drivers for this season, find the most recent season with drivers
                    if ($driversForThisSeason->isEmpty() && $hasAnyDrivers) {
                        $mostRecentWithDrivers = DB::table('driver_constructor_seasons')
                            ->join('seasons', 'driver_constructor_seasons.season_id', '=', 'seasons.season_id') // Note: season_id, not id
                            ->where('driver_constructor_seasons.constructor_id', $constructor->constructor_id)
                            ->orderBy('seasons.year', 'desc')
                            ->select('seasons.*')
                            ->first();
                            
                        if ($mostRecentWithDrivers) {
                            $mostRecentYear = $mostRecentWithDrivers->year;
                            $mostRecentSeasonId = $mostRecentWithDrivers->season_id;
                            
                            Log::info("Using fallback season {$mostRecentYear} (ID: {$mostRecentSeasonId}) for {$constructor->name}");
                            
                            // Update driver query to use the most recent season with drivers
                            $driversForThisSeason = DB::table('driver_constructor_seasons')
                                ->where('constructor_id', $constructor->constructor_id)
                                ->where('season_id', $mostRecentSeasonId)
                                ->get();
                                
                            // Update display year
                            $seasonYear = $mostRecentYear . ' (most recent)';
                        }
                    }
                    
                    // Get full driver details with nationality
                    $driversList = [];
                    
                    foreach ($driversForThisSeason as $driverSeason) {
                        $driver = DB::table('drivers')
                            ->leftJoin('countries', 'drivers.nationality', '=', 'countries.country_id')
                            ->where('drivers.driver_id', $driverSeason->driver_id)
                            ->select(
                                'drivers.*', 
                                'countries.name as nationality_name',
                                'countries.code as nationality_code'
                            )
                            ->first();
                            
                        if ($driver) {
                            // Add pivot data
                            $driver->position_number = $driverSeason->position_number;
                            
                            // Structure nationality for frontend
                            $driver->nationality = [
                                'name' => $driver->nationality_name,
                                'code' => $driver->nationality_code
                            ];
                            
                            // Add to drivers list
                            $driversList[] = $driver;
                        }
                    }
                    
                    // Set the data on the constructor
                    $constructor->setAttribute('current_drivers', $driversList);
                    $constructor->setAttribute('driver_season_year', $seasonYear);
                    
                    // Debug what we found
                    Log::info("Setting {$constructor->name} with " . count($driversList) . 
                        " drivers from year {$seasonYear}");
                } else {
                    // No valid season found
                    $constructor->setAttribute('current_drivers', []);
                    $constructor->setAttribute('driver_season_year', 'No data');
                }
            } catch (\Exception $e) {
                Log::error("Error processing drivers for {$constructor->name}: " . $e->getMessage());
                $constructor->setAttribute('current_drivers', []);
            }
        }
        
        return ConstructorResource::collection($constructors);
    } catch (\Exception $e) {
        Log::error('Constructor API error: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return ConstructorResource::collection($constructors->toArray());
    }
  }

  public function store(Request $request)
  {
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'required|exists:countries,country_id',
            'base' => 'nullable|string|max:255',
            'first_team_entry' => 'nullable|string|max:4',
            'team_chief' => 'nullable|string|max:255',
            'technical_chief' => 'nullable|string|max:255',
            'chassis' => 'nullable|string|max:255',
            'power_unit' => 'nullable|string|max:255',
            'official_website' => 'nullable|string|max:255',
            'image_file' => 'nullable|file|image|max:2048',
        ]);

        // Crear constructor sin imagen primero
        $constructor = Constructor::create([
            'name' => $validated['name'],
            'nationality' => $validated['nationality'],
            'base' => $validated['base'] ?? null,
            'first_team_entry' => $validated['first_team_entry'] ?? null,
            'team_chief' => $validated['team_chief'] ?? null,
            'technical_chief' => $validated['technical_chief'] ?? null,
            'chassis' => $validated['chassis'] ?? null,
            'power_unit' => $validated['power_unit'] ?? null,
            'official_website' => $validated['official_website'] ?? null,
        ]);

        // Procesar imagen si existe
        if ($request->hasFile('image_file')) {
            // Generar nombre de archivo único
            $fileName = 'constructor_' . time() . '_' . $constructor->constructor_id . '.' . $request->file('image_file')->getClientOriginalExtension();
            
            // Guardar archivo
            $path = $request->file('image_file')->storeAs('constructors', $fileName, 'public');
            
            // Actualizar modelo con la ruta
            $constructor->logo = $path;
            $constructor->save();
        }

        return new ConstructorResource($constructor->load('country'));
    } catch (\Exception $e) {
        \Log::error('Error creating constructor:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'Error creating constructor',
            'error' => $e->getMessage()
        ], 500);
    }
  }

  public function show(Constructor $constructor): ConstructorResource
  {
    return new ConstructorResource($constructor->load(['country', 'drivers', 'currentDrivers']));
  }

  public function update(Request $request, Constructor $constructor): ConstructorResource
  {
    $validated = $request->validate([
      'name' => 'sometimes|required|string|max:255',
      'nationality' => 'nullable|exists:countries,country_id',
      'logo' => 'nullable|string|max:255',
      'base' => 'nullable|string|max:255',
      'first_team_entry' => 'nullable|digits:4',
      'team_chief' => 'nullable|string|max:255',
      'technical_chief' => 'nullable|string|max:255',
      'chassis' => 'nullable|string|max:255',
      'power_unit' => 'nullable|string|max:255',
      'official_website' => 'nullable|string|max:255',
    ]);

    $constructor->update($validated);

    return new ConstructorResource($constructor->load('country'));
  }

  public function destroy(Constructor $constructor): Response
  {
    $constructor->delete();

    return response()->noContent();
  }
}

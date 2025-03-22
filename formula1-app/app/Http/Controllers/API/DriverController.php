<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DriverController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return DriverResource::collection(
      Driver::with(['country'])->orderBy('active', 'desc')->orderBy('last_name')->get()
    );
  }

  public function store(Request $request): DriverResource
  {
    $validated = $request->validate([
      'first_name' => 'required|string|max:100',
      'last_name' => 'required|string|max:100',
      'code' => 'nullable|string|size:3',
      'number' => 'nullable|integer',
      'date_of_birth' => 'nullable|date',
      'nationality' => 'nullable|exists:countries,country_id',
      'active' => 'nullable|boolean',
      'biography' => 'nullable|string',
      'image' => 'nullable|string|max:255',
      'debut_year' => 'nullable|digits:4',
      'championships' => 'nullable|integer|min:0',
      'wins' => 'nullable|integer|min:0',
      'podiums' => 'nullable|integer|min:0',
      'poles' => 'nullable|integer|min:0',
      'fastest_laps' => 'nullable|integer|min:0',
    ]);

    $driver = Driver::create($validated);

    return new DriverResource($driver->load('country'));
  }

  public function show(Driver $driver): DriverResource
  {
    return new DriverResource($driver->load([
      'country',
      'constructors',
      'championships'
    ]));
  }

  public function update(Request $request, Driver $driver): DriverResource
  {
    $validated = $request->validate([
      'first_name' => 'sometimes|required|string|max:100',
      'last_name' => 'sometimes|required|string|max:100',
      'code' => 'nullable|string|size:3',
      'number' => 'nullable|integer',
      'date_of_birth' => 'nullable|date',
      'nationality' => 'nullable|exists:countries,country_id',
      'active' => 'nullable|boolean',
      'biography' => 'nullable|string',
      'image' => 'nullable|string|max:255',
      'debut_year' => 'nullable|digits:4',
      'championships' => 'nullable|integer|min:0',
      'wins' => 'nullable|integer|min:0',
      'podiums' => 'nullable|integer|min:0',
      'poles' => 'nullable|integer|min:0',
      'fastest_laps' => 'nullable|integer|min:0',
    ]);

    $driver->update($validated);

    return new DriverResource($driver->load('country'));
  }

  public function destroy(Driver $driver): Response
  {
    $driver->delete();

    return response()->noContent();
  }
}

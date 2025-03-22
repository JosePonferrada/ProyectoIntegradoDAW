<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CountryController extends Controller
{
  public function index(): AnonymousResourceCollection
  {
    return CountryResource::collection(Country::all());
  }

  public function store(Request $request): CountryResource
  {
    $validated = $request->validate([
      'name' => 'required|string|max:100',
      'code' => 'required|string|size:2',
      'flag_img' => 'nullable|string|max:255'
    ]);

    $country = Country::create($validated);

    return new CountryResource($country);
  }

  public function show(Country $country): CountryResource
  {
    return new CountryResource($country);
  }

  public function update(Request $request, Country $country): CountryResource
  {
    $validated = $request->validate([
      'name' => 'sometimes|required|string|max:100',
      'code' => 'sometimes|required|string|size:2',
      'flag_img' => 'nullable|string|max:255'
    ]);

    $country->update($validated);

    return new CountryResource($country);
  }

  public function destroy(Country $country): Response
  {
    $country->delete();

    return response()->noContent();
  }
}

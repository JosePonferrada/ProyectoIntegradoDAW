<?php

namespace App\Http\Controllers\Formula1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Race;

class RaceController extends Controller
{
    public function show($id)
    {
        // Solo pasamos el ID a la vista de Inertia
        return Inertia::render('Formula1/RaceDetails', [
            'id' => (int)$id
        ]);
    }

    public function create()
    {
        return Inertia::render('Formula1/RaceCreate');
    }

    public function edit(Request $request, $id)
    {
        return Inertia::render('Formula1/RaceEdit', [
            'id' => $id
        ]);
    }
}
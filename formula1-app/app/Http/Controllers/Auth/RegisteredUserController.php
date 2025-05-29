<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // Cargar datos para selects
        $countries = \App\Models\Country::all();
        $drivers = \App\Models\Driver::select('driver_id', 'first_name', 'last_name')->get();
        $constructors = \App\Models\Constructor::select('constructor_id', 'name')->get();
        
        return Inertia::render('auth/Register', [
            'countries' => $countries,
            'drivers' => $drivers,
            'constructors' => $constructors,
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => 'nullable|integer|exists:countries,country_id', // Cambiar a country_id
            'favorite_driver_id' => 'nullable|integer|exists:drivers,driver_id',
            'favorite_constructor_id' => 'nullable|integer|exists:constructors,constructor_id',
            'avatar' => 'nullable|image|max:2048', // Validación para imágenes, máximo 2MB
        ]);
        
        // Gestionar el avatar si se sube uno
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_id' => $request->country_id, // Cambiar a country_id
            'favorite_driver_id' => $request->favorite_driver_id,
            'favorite_constructor_id' => $request->favorite_constructor_id,
            'avatar' => $avatarPath,
            'role' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

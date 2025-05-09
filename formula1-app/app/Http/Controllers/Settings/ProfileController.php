<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'countries' => \App\Models\Country::all(),
            'drivers' => \App\Models\Driver::select('driver_id', 'first_name', 'last_name')->get(),
            'constructors' => \App\Models\Constructor::select('constructor_id', 'name')->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'country_id' => 'nullable|exists:countries,country_id',
            'favorite_driver_id' => 'nullable|exists:drivers,driver_id',
            'favorite_constructor_id' => 'nullable|exists:constructors,constructor_id',
            'avatar' => 'nullable|sometimes|image|max:2048',
        ]);

        $user = $request->user();
        
        // Gestión de avatar
        if ($request->input('avatar') === 'remove') {
            // Si hay avatar previo, eliminarlo
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        } 
        elseif ($request->hasFile('avatar')) {
            // Eliminar avatar anterior
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
        
        // Actualizar campos
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->country_id = $validated['country_id'];
        $user->favorite_driver_id = $validated['favorite_driver_id'];
        $user->favorite_constructor_id = $validated['favorite_constructor_id'];
        
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        
        return back()->with([
            'status' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show the user's profile.
     */
    public function show($id = null)
    {
        // Si no se proporciona ID, usa el usuario actual
        $userId = $id ?? auth()->id();
        
        // Cargar usuario con relaciones
        $user = User::with(['country', 'favoriteDriver', 'favoriteConstructor'])
                    ->findOrFail($userId);
                    
        return Inertia::render('Profile/Show', [
            'user' => $user,
        ]);
    }
}

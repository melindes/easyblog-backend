<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ✅ INSCRIPTION
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
            'mode'     => 'reader',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

       return response()->json([
    'message' => 'Inscription réussie',
    'token'   => $token,
    'user'    => new UserResource($user),
], 201);
    }

    // ✅ CONNEXION
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
    'message' => 'Connexion réussie',
    'token'   => $token,
    'user'    => new UserResource($user),
]);
    }

    // ✅ DÉCONNEXION
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

  public function switchMode(Request $request)
{
    $user = $request->user();

    if ($user->role === 'admin') {
        return response()->json(['message' => 'Admin ne change pas de mode'], 403);
    }

    if ($user->is_blocked) {
        return response()->json(['message' => 'Mode auteur bloqué'], 403);
    }

    if ($user->mode === 'reader') {
        $user->update(['mode' => 'author']); 
         $user->refresh(); // ← bien sauvegardé en BD ?
        return response()->json([
            'message' => 'Mode auteur activé',
            'user'    => $user,
        ]);
    }

    if ($user->mode === 'author') {
        $user->update(['mode' => 'reader']);
         $user->refresh();
        return response()->json([
            'message' => 'Mode lecteur activé',
            'user'    => $user,
        ]);
    }
}
}
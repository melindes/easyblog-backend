<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ✅ Middleware admin pour toutes les méthodes
    private function checkAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return false;
        }
        return true;
    }

    // ✅ Bloquer le mode auteur
    public function blockAuthor(Request $request, User $user)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json([
                'message' => 'Action réservée à l\'administrateur'
            ], 403);
        }

        // Ne peut pas bloquer un admin
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Impossible de bloquer un administrateur'
            ], 403);
        }

        $user->update([
            'is_blocked' => true,
            'mode'       => 'reader', // ← repasse en reader
        ]);

        return response()->json([
            'message' => "Mode auteur de {$user->name} bloqué avec succès",
            'user'    => $user,
        ]);
    }

    // ✅ Débloquer le mode auteur
    public function unblockAuthor(Request $request, User $user)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json([
                'message' => 'Action réservée à l\'administrateur'
            ], 403);
        }

        $user->update(['is_blocked' => false]);

        return response()->json([
            'message' => "Mode auteur de {$user->name} débloqué avec succès",
            'user'    => $user,
        ]);
    }

    // ✅ Liste de tous les utilisateurs
    public function users(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json([
                'message' => 'Action réservée à l\'administrateur'
            ], 403);
        }

        $users = User::where('role', '!=', 'admin')->get();

        return response()->json(['users' => $users]);
    }
}
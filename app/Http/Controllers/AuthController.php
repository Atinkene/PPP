<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

// Contrôleur pour gérer l'authentification
class AuthController extends Controller
{
    // Connexion de l'utilisateur
    public function connecter(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required',
        ]);

        $utilisateur = User::where('email', $request->email)->first();

        if (!$utilisateur || !Hash::check($request->mot_de_passe, $utilisateur->password)) {
            return response()->json(['erreur' => 'Identifiants invalides'], 401);
        }

        $jeton = $utilisateur->createToken('jeton_auth')->plainTextToken;

        return response()->json(['jeton_acces' => $jeton, 'type_jeton' => 'Bearer']);
    }

    // Déconnexion de l'utilisateur
    public function deconnecter(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }
}
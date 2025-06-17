<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function connecter(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required',
        ]);

        $utilisateur = User::where('email', $request->email)->first();

        if (!$utilisateur || !Hash::check($request->mot_de_passe, $utilisateur->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Indiquer le rôle dans la réponse peut être utile
        $role = $utilisateur->role;
        $jeton = $utilisateur->createToken('jeton_auth')->plainTextToken;

        return response()->json([
            'token' => $jeton,
            'type_jeton' => 'Bearer',
            'utilisateur' => $utilisateur->only(['id', 'nom', 'prenom', 'email', 'role']),
        ]);
    }

    public function deconnecter(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
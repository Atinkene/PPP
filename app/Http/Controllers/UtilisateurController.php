<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités communes à tous les utilisateurs
class UtilisateurController extends Controller
{
    // Consulter les données personnelles
    public function consulterDonneesPersonnelles(Request $request)
    {
        return response()->json($request->user());
    }

    // Modifier les données personnelles
    public function modifierDonneesPersonnelles(Request $request)
    {
        $request->validate([
            'nom' => 'string',
            'prenom' => 'string',
            'adresse_postale' => 'string|nullable',
            'numero_telephone' => 'string|nullable',
        ]);

        $utilisateur = $request->user();
        $utilisateur->update($request->only(['nom', 'prenom', 'adresse_postale', 'numero_telephone']));
        return response()->json($utilisateur);
    }

    // Consulter les rendez-vous
    public function consulterRendezVous(Request $request)
    {
        $utilisateur = $request->user();
        $rendezVous = $utilisateur->patient 
            ? $utilisateur->patient->rendezVous 
            : $utilisateur->professionnelSante->rendezVous;
        return response()->json($rendezVous);
    }
}
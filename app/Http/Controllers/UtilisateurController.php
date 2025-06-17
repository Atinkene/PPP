<?php

   namespace App\Http\Controllers;

   use App\Models\User;
    use App\Models\Etablissement;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Auth;
   use Illuminate\Support\Facades\Log;

   class UtilisateurController extends Controller
   {
       public function consulterDonneesPersonnelles(Request $request)
       {
           $user = Auth::user();
           if (!$user) {
               Log::error('Aucun utilisateur authentifié pour /api/donnees-personnelles', [
                   'token' => $request->bearerToken(),
                   'headers' => $request->headers->all(),
               ]);
               return response()->json(['message' => 'Non autorisé'], 401);
           }

           Log::info('Utilisateur authentifié pour /api/donnees-personnelles', [
               'user_id' => $user->id,
               'role' => $user->role,
           ]);

           return response()->json([
               'email' => $user->email,
               'nom' => $user->nom,
               'prenom' => $user->prenom,
               'sexe' => $user->sexe,
               'date_naissance' => $user->date_naissance,
               'cin' => $user->cin,
               'lieu_naissance' => $user->lieu_naissance,
               'nationalite' => $user->nationalite,
               'adresse_postale' => $user->adresse_postale,
               'numero_telephone' => $user->numero_telephone,
               'login' => $user->login,
               'role' => $user->role,
           ]);
       }

       public function modifierDonneesPersonnelles(Request $request)
       {
           $request->validate([
               'nom' => 'string',
               'prenom' => 'string',
               'adresse_postale' => 'string|nullable',
               'numero_telephone' => 'string|nullable',
           ]);

           $user = $request->user();
           if (!$user) {
               return response()->json(['message' => 'Non autorisé'], 401);
           }

           $user->update($request->only(['nom', 'prenom', 'adresse_postale', 'numero_telephone']));
           return response()->json($user);
       }

       public function consulterRendezVous(Request $request)
       {
           $user = $request->user();
           if (!$user) {
               return response()->json(['message' => 'Non autorisé'], 401);
           }

           $rendezVous = $user->rendezVous()->orderBy('date', 'desc')->get();
           return response()->json($rendezVous);
       }

        public function getEtablissements()
       {
           $etab = Etablissement::all();
           return response()->json($etab);
       }
   }
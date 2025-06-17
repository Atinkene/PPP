<?php

namespace App\Http\Controllers;

use App\Models\ActeurNonMedical;
use App\Models\Patient;
use App\Models\ProfessionnelSante;
use App\Models\User;
use App\Services\ServiceRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InscriptionController extends Controller
{
    protected $serviceRole;

    public function __construct(ServiceRole $serviceRole)
    {
        $this->serviceRole = $serviceRole;
    }

    public function inscrire(Request $request)
    {
        $nonMedicalRoles = ['personnel_administratif'];
        $request->validate([
            'email' => 'required|email|unique:users',
            'mot_de_passe' => 'required|min:6',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string',
            'date_naissance' => 'required|date',
            'cin' => 'required|string|unique:users',
            'lieu_naissance' => 'required|string',
            'nationalite' => 'required|string',
            'role' => 'required|in:patient,anesthesiste,assistant_medical,biologiste,chirurgien,infirmier,kinesitherapeute,medecin,personnel_administratif,radiologue,psychologue',
            'adresse_postale' => 'nullable|string',
            'numero_telephone' => 'nullable|string',
            'login' => 'required|string|unique:users',
        ]);

        $utilisateur = User::create([
            'id' => Str::uuid(),
            'email' => $request->email,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'date_naissance' => $request->date_naissance,
            'cin' => $request->cin,
            'lieu_naissance' => $request->lieu_naissance,
            'nationalite' => $request->nationalite,
            'adresse_postale' => $request->adresse_postale,
            'numero_telephone' => $request->numero_telephone,
            'login' => $request->login,
            'password' => Hash::make($request->mot_de_passe),
            'role' => $request->role,
        ]);

        if ($request->role === 'patient') {
            Patient::create([
                'id' => Str::uuid(),
                'id_user' => $utilisateur->id,
                'groupe_sanguin' => $request->groupe_sanguin,
            ]);
        } elseif (in_array($request->role, $nonMedicalRoles)) {
            ActeurNonMedical::create([
                'id' => Str::uuid(),
                'id_user' => $utilisateur->id,
                'role' => $request->role,
                'numero_adeli' => $request->numero_adeli,
                'id_service' => 'f4cf014e-eb9a-47d6-9712-764afeb26436',
            ]);
        } else {
            ProfessionnelSante::create([
                'id' => Str::uuid(),
                'id_user' => $utilisateur->id,
                'type' => $request->role,
                'numero_rpps' => $request->numero_rpps,
                'specialite' => $request->specialite,
                'id_service' => 'f4cf014e-eb9a-47d6-9712-764afeb26436',
            ]);
        }

        $jeton = $utilisateur->createToken('jeton_auth')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur inscrit',
            'utilisateur' => $utilisateur,
            'token' => $jeton,
            'type_jeton' => 'Bearer'
        ], 201);
    }
}
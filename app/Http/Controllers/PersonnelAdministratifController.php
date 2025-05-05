<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Assurance;
use App\Models\Contact;
use App\Models\DossierAdministratif;
use App\Models\DossierPatient;
use App\Models\User;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Personnel Administratif
class PersonnelAdministratifController extends Controller
{
    // Mettre à jour le service de destination
    public function mettreAJourServiceDestination(Request $request, $idAdmission)
    {
        $request->validate(['id_service' => 'required|uuid']);
        $admission = Admission::findOrFail($idAdmission);
        $admission->update(['id_service' => $request->id_service]);
        return response()->json($admission);
    }

    // Enregistrer les données personnelles
    public function enregistrerDonneesPersonnelles(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => 'required|string',
            'date_naissance' => 'required|date',
            'cin' => 'required|string|unique:users',
            'lieu_naissance' => 'required|string',
            'nationalite' => 'required|string',
        ]);

        $utilisateur = User::create([
            'id' => \Str::uuid(),
            'email' => $request->email,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'sexe' => $request->sexe,
            'date_naissance' => $request->date_naissance,
            'cin' => $request->cin,
            'lieu_naissance' => $request->lieu_naissance,
            'nationalite' => $request->nationalite,
            'password' => bcrypt('default_password'), // À modifier selon la logique
        ]);

        return response()->json($utilisateur);
    }

    // Enregistrer les données d'assurance
    public function enregistrerDonneesAssurance(Request $request)
    {
        $request->validate([
            'numero_securite_social' => 'string|nullable',
            'organisme_assurance_sante' => 'string|nullable',
            'prise_en_charge' => 'numeric|nullable',
        ]);

        $assurance = Assurance::create([
            'id' => \Str::uuid(),
            'numero_securite_social' => $request->numero_securite_social,
            'organisme_assurance_sante' => $request->organisme_assurance_sante,
            'prise_en_charge' => $request->prise_en_charge,
        ]);

        return response()->json($assurance);
    }

    // Consulter les données administratives
    public function consulterDonneesAdministratives($idPatient)
    {
        $donnees = DossierAdministratif::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($donnees);
    }

    // Planifier une admission
    public function planifierAdmission(Request $request, $idPatient)
    {
        $request->validate(['motif' => 'string|nullable', 'date' => 'required|date']);
        $admission = Admission::create([
            'id' => \Str::uuid(),
            'id_dossier_admission' => DossierAdmissionSejour::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'motif' => $request->motif,
            'date' => $request->date,
        ]);
        return response()->json($admission);
    }

    // Mettre à jour les données financières
    public function mettreAJourDonneesFinancieres(Request $request, $idAssurance)
    {
        $request->validate(['prise_en_charge' => 'numeric|nullable']);
        $assurance = Assurance::findOrFail($idAssurance);
        $assurance->update(['prise_en_charge' => $request->prise_en_charge]);
        return response()->json($assurance);
    }

    // Créer un dossier patient
    public function creerDossierPatient(Request $request)
    {
        $request->validate(['id_user' => 'required|uuid']);
        $dossier = DossierPatient::create([
            'id' => \Str::uuid(),
            'id_patient' => Patient::where('id_user', $request->id_user)->first()->id,
        ]);
        return response()->json($dossier);
    }

    // Planifier une sortie
    public function planifierSortie(Request $request, $idPatient)
    {
        $request->validate(['date' => 'required|date']);
        $compteRendu = CompteRenduSortie::create([
            'id' => \Str::uuid(),
            'id_dossier_sortie' => DossierSortieSuivi::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'date' => $request->date,
        ]);
        return response()->json($compteRendu);
    }
}
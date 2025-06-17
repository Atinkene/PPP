<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\Patient;
use App\Models\Consentement;
use App\Models\DossierPatient;
use App\Models\Medicament;
use App\Models\RendezVous;
use App\Models\ExamenImagerie;
use App\Models\CompteRenduSortie;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Patient
class PatientController extends Controller
{
    // Prendre un rendez-vous
    public function prendreRendezVous(Request $request)
    {
        $request->validate([
            'id_etablissement' => 'required|uuid',
            'date' => 'required|date',
            'type' => 'required|string',
        ]);

        $rendezVous = RendezVous::create([
            'id' => \Str::uuid(),
            'id_patient' => auth()->user()->patient->id,
            'id_etablissement' => $request->id_etablissement,
            'date' => $request->date,
            'type' => $request->type,
            'statut' => 'en_attente',
            'id_dossier_patient' => $request->id_dossier_patient,
        ]);

        return response()->json($rendezVous);
    }

    // Annuler un rendez-vous
    public function annulerRendezVous($id)
    {
        $rendezVous = RendezVous::where('id_patient', auth()->user()->patient->id)->findOrFail($id);
        $rendezVous->update(['statut' => 'annulé']);
        return response()->json($rendezVous);
    }

    // Consulter les données personnelles
    public function consulterDonneesPersonnelles()
    {
        return response()->json(auth()->user());
    }

    // Consulter les antécédents médicaux
    public function consulterAntecedentsMedicaux()
    {
        $antecedents = Antecedent::where('id_patient', auth()->user()->patient->id)->get();
        return response()->json($antecedents);
    }

    // Consulter les ordonnances
    public function consulterOrdonnances()
    {
        $ordonnances = Medicament::whereHas('traitement.dossierSoins.dossier.patient', fn($q) => $q->where('id', auth()->user()->patient->id))->get();
        return response()->json($ordonnances);
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

        $utilisateur = auth()->user();
        $utilisateur->update($request->only(['nom', 'prenom', 'adresse_postale', 'numero_telephone']));
        return response()->json($utilisateur);
    }

    // Fournir des antécédents médicaux
    public function fournirAntecedentsMedicaux(Request $request)
    {
        $request->validate(['maladie' => 'required|string']);
        $antecedent = Antecedent::create([
            'id' => \Str::uuid(),
            'id_patient' => auth()->user()->patient->id,
            'maladie' => $request->maladie,
        ]);
        return response()->json($antecedent);
    }

    // Consulter les instructions de sortie
    public function consulterInstructionsSortie()
    {
        $instructions = CompteRenduSortie::whereHas('dossierSortie.dossier.patient', fn($q) => $q->where('id', auth()->user()->patient->id))->get();
        return response()->json($instructions);
    }

    // Consulter les résultats d'examens
    public function consulterResultatsExamens()
    {
        $resultats = ExamenImagerie::whereHas('dossierExamen.dossier.patient', fn($q) => $q->where('id', auth()->user()->patient->id))->get();
        return response()->json($resultats);
    }

    // Enregistrer un consentement
    public function enregistrerConsentement(Request $request)
    {
        $request->validate(['type' => 'required|string', 'statut' => 'required|string']);
        $consentement = Consentement::create([
            'id' => \Str::uuid(),
            'type' => $request->type,
            'statut' => $request->statut,
            'date_autorisation' => now(),
        ]);
        return response()->json($consentement);
    }

    // Demander une copie du dossier médical
    public function demanderCopieDossier()
    {
        $dossier = DossierPatient::where('id_patient', auth()->user()->patient->id)->first();
        return response()->json($dossier);
    }

    // Signaler une erreur
    public function signalerErreur(Request $request)
    {
        $request->validate(['description' => 'required|string']);
        // Logique pour enregistrer l'erreur (par exemple, dans une table dédiée)
        return response()->json(['message' => 'Erreur signalée']);
    }

    // Évaluer la prise en charge
    public function evaluerPriseEnCharge(Request $request)
    {
        $request->validate(['note' => 'required|integer|min:1|max:5', 'commentaire' => 'string|nullable']);
        // Logique pour enregistrer l'évaluation
        return response()->json(['message' => 'Évaluation enregistrée']);
    }


    public function getDossierActifPatient($id_user)
    {
        $idPatient = Patient::where('id_user', $id_user)->value('id');
        $dossier = DossierPatient::where('id_patient', $idPatient)->first();
        return response()->json($dossier);
    }

    public function mettreAJourRendezVous($idrv, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $rendezVous = RendezVous::findOrFail($idRv);
        $rendezVous->update([
            'date' => $request->date,
        ]);

        return response()->json($rendezVous);
    }
    
}
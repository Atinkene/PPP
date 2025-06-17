<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\Medicament;
use App\Models\ObservationInfirmiere;
use App\Models\RendezVous;
use App\Models\SuiviHospitalier;
use App\Models\Traitement;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités de l'Infirmier
class InfirmierController extends Controller
{
    // Mettre à jour le suivi hospitalier
    public function mettreAJourSuiviHospitalier(Request $request, $idDossierAdmission)
    {
        $request->validate(['observation' => 'required|string']);
        $suivi = SuiviHospitalier::create([
            'id' => \Str::uuid(),
            'id_dossier_admission' => $idDossierAdmission,
            'observation_medicale' => $request->observation,
        ]);
        return response()->json($suivi);
    }

    // Annuler un rendez-vous
    public function annulerRendezVous($id)
    {
        $rendezVous = RendezVous::findOrFail($id);
        $rendezVous->update(['statut' => 'annulé']);
        return response()->json($rendezVous);
    }

    // Consulter les rendez-vous
    public function consulterRendezVous()
    {
        $rendezVous = RendezVous::all();
        return response()->json($rendezVous);
    }

    // Consulter les prescriptions
    public function consulterPrescriptions($idPatient)
    {
        $prescriptions = Medicament::whereHas('traitement.dossierSoins.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($prescriptions);
    }

    // Consulter les antécédents
    public function consulterAntecedents($idPatient)
    {
        $antecedents = Antecedent::where('id_patient', $idPatient)->get();
        return response()->json($antecedents);
    }

    // Enregistrer les signes vitaux
    public function enregistrerSignesVitaux(Request $request, $idPatient)
    {
        $request->validate(['signes_vitaux' => 'required|string']);
        $observation = ObservationInfirmiere::create([
            'id' => \Str::uuid(),
            'id_dossier_infirmier' => DossierSoinsInfirmier::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'etat_general' => $request->signes_vitaux,
            'date' => now(),
        ]);
        return response()->json($observation);
    }

    // Enregistrer l'état général
    public function enregistrerEtatGeneral(Request $request, $idPatient)
    {
        $request->validate(['etat_general' => 'required|string']);
        $observation = ObservationInfirmiere::create([
            'id' => \Str::uuid(),
            'id_dossier_infirmier' => DossierSoinsInfirmier::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'etat_general' => $request->etat_general,
            'date' => now(),
        ]);
        return response()->json($observation);
    }

    // Enregistrer la douleur
    public function enregistrerDouleur(Request $request, $idPatient)
    {
        $request->validate(['douleur' => 'required|string']);
        $observation = ObservationInfirmiere::create([
            'id' => \Str::uuid(),
            'id_dossier_infirmier' => DossierSoinsInfirmier::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'etat_general' => $request->douleur,
            'date' => now(),
        ]);
        return response()->json($observation);
    }

    // Enregistrer l'administration d'un médicament
    public function enregistrerAdministrationMedicament(Request $request, $idMedicament)
    {
        $request->validate(['dose' => 'required|string']);
        $administration = AdministrationMedicament::create([
            'id' => \Str::uuid(),
            'id_medicament' => $idMedicament,
            'id_dossier_infirmier' => DossierSoinsInfirmier::whereHas('dossier.patient', fn($q) => $q->where('id', auth()->user()->patient->id))->first()->id,
            'dose' => $request->dose,
            'date_administration' => now(),
        ]);
        return response()->json($administration);
    }

    // Enregistrer un soin réalisé
    public function enregistrerSoinRealise(Request $request, $idPatient)
    {
        $request->validate(['description' => 'required|string']);
        $traitement = Traitement::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => 'soin',
            'description' => $request->description,
            'date' => now(),
        ]);
        return response()->json($traitement);
    }

    // Signaler une complication
    public function signalerComplication(Request $request, $idPatient)
    {
        $request->validate(['description' => 'required|string']);
        // Logique pour signaler la complication (par exemple, notification ou table dédiée)
        return response()->json(['message' => 'Complication signalée']);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Medicament;
use App\Models\SuiviHospitalier;
use App\Models\Traitement;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Kinésithérapeute
class KinesitherapeuteController extends Controller
{
    // Consulter les prescriptions
    public function consulterPrescriptions($idPatient)
    {
        $prescriptions = Medicament::whereHas('traitement.dossierSoins.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($prescriptions);
    }

    // Consulter l'état post-opératoire
    public function consulterEtatPostOperatoire($idPatient)
    {
        $etat = SuiviHospitalier::whereHas('dossierAdmission.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($etat);
    }

    // Enregistrer une séance de rééducation
    public function enregistrerSeanceReeducation(Request $request, $idPatient)
    {
        $request->validate(['description' => 'required|string']);
        $traitement = Traitement::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => 'rééducation',
            'description' => $request->description,
            'date' => now(),
        ]);
        return response()->json($traitement);
    }

    // Enregistrer les progrès
    public function enregistrerProgres(Request $request, $idPatient)
    {
        $request->validate(['progres' => 'required|string']);
        $suivi = SuiviHospitalier::create([
            'id' => \Str::uuid(),
            'id_dossier_admission' => DossierAdmissionSejour::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'evolution_clinique' => $request->progres,
        ]);
        return response()->json($suivi);
    }

    // Recommander un suivi
    public function recommanderSuivi(Request $request, $idPatient)
    {
        $request->validate(['recommandation' => 'required|string']);
        $traitement = Traitement::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => 'suivi',
            'description' => $request->recommandation,
            'date' => now(),
        ]);
        return response()->json($traitement);
    }
}
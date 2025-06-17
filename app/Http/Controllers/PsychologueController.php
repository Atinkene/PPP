<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\EvaluationPsychologique;
use App\Models\Traitement;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Psychologue
class PsychologueController extends Controller
{
    // Consulter les antécédents médicaux
    public function consulterAntecedentsMedicaux($idPatient)
    {
        $antecedents = Antecedent::where('id_patient', $idPatient)->get();
        return response()->json($antecedents);
    }

    // Consulter les données sociales
    public function consulterDonneesSociales($idPatient)
    {
        $donnees = EvaluationPsychologique::whereHas('dossierPsycho.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($donnees);
    }

    // Enregistrer un diagnostic psychologique
    public function enregistrerDiagnosticPsychologique(Request $request, $idPatient)
    {
        $request->validate(['diagnostic' => 'required|string']);
        $evaluation = EvaluationPsychologique::create([
            'id' => \Str::uuid(),
            'id_dossier_psycho' => DossierPsychoSociaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'diagnostic' => $request->diagnostic,
            'date' => now(),
        ]);
        return response()->json($evaluation);
    }

    // Enregistrer une prescription psychologique
    public function enregistrerPrescriptionPsychologique(Request $request, $idPatient)
    {
        $request->validate(['description' => 'required|string']);
        $traitement = Traitement::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => 'psychologique',
            'description' => $request->description,
            'date' => now(),
        ]);
        return response()->json($traitement);
    }

    // Rédiger un compte rendu de séance
    public function redigerCompteRenduSeance(Request $request, $idPatient)
    {
        $request->validate(['suivi' => 'required|string']);
        $evaluation = EvaluationPsychologique::create([
            'id' => \Str::uuid(),
            'id_dossier_psycho' => DossierPsychoSociaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'suivi' => $request->suivi,
            'date' => now(),
        ]);
        return response()->json($evaluation);
    }

    // Ajouter une note sociale
    public function ajouterNoteSociale(Request $request, $idPatient)
    {
        $request->validate(['note' => 'required|string']);
        $evaluation = EvaluationPsychologique::create([
            'id' => \Str::uuid(),
            'id_dossier_psycho' => DossierPsychoSociaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'suivi' => $request->note,
            'date' => now(),
        ]);
        return response()->json($evaluation);
    }
}
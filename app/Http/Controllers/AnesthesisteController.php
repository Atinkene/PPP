<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\FichePreoperatoire;
use App\Models\Operation;
use App\Models\Traitement;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités de l'Anesthésiste
class AnesthesisteController extends Controller
{
    // Consulter les antécédents d'un patient
    public function consulterAntecedents($idPatient)
    {
        $antecedents = Antecedent::where('id_patient', $idPatient)->get();
        return response()->json($antecedents);
    }

    // Consulter les examens préopératoires
    public function consulterExamensPreoperatoires($idPatient)
    {
        $examens = FichePreoperatoire::whereHas('dossierChirurgie.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($examens);
    }

    // Enregistrer une évaluation anesthésique
    public function enregistrerEvaluationAnesthesique(Request $request, $idPatient)
    {
        $request->validate(['evaluation' => 'required|string']);
        $operation = Operation::create([
            'id' => \Str::uuid(),
            'id_antecedent' => Antecedent::where('id_patient', $idPatient)->first()->id,
            'nom' => 'Évaluation anesthésique',
            'date' => now(),
        ]);
        return response()->json($operation);
    }

    // Enregistrer les paramètres pendant l'intervention
    public function enregistrerParametresIntervention(Request $request, $idOperation)
    {
        $request->validate(['parametres' => 'required|string']);
        $operation = Operation::findOrFail($idOperation);
        $operation->update(['description' => $request->parametres]);
        return response()->json($operation);
    }

    // Enregistrer le compte rendu anesthésique
    public function enregistrerCompteRenduAnesthesique(Request $request, $idOperation)
    {
        $request->validate(['compte_rendu' => 'required|string']);
        $operation = Operation::findOrFail($idOperation);
        $operation->update(['compte_rendu' => $request->compte_rendu]);
        return response()->json($operation);
    }

    // Prescrire des soins post-anesthésiques
    public function prescrireSoinPostAnesthesique(Request $request, $idPatient)
    {
        $request->validate(['description' => 'required|string']);
        $traitement = Traitement::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => 'post-anesthésique',
            'date' => now(),
            'description' => $request->description,
        ]);
        return response()->json($traitement);
    }
}
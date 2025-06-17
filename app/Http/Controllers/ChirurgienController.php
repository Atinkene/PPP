<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\Consentement;
use App\Models\ExamenImagerie;
use App\Models\FichePreoperatoire;
use App\Models\Operation;
use App\Models\Traitement;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Chirurgien
class ChirurgienController extends Controller
{
    // Consulter les antécédents
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

    // Enregistrer une fiche préopératoire
    public function enregistrerFichePreoperatoire(Request $request, $idPatient)
    {
        $request->validate(['nom' => 'required|string', 'date' => 'required|date']);
        $fiche = FichePreoperatoire::create([
            'id' => \Str::uuid(),
            'id_dossier_chirurgie' => DossierChirurgieAnesthesie::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'nom' => $request->nom,
            'date' => $request->date,
        ]);
        return response()->json($fiche);
    }

    // Consulter les consentements du patient
    public function consulterConsentements($idPatient)
    {
        $consentements = Consentement::whereHas('dossierAdministratif.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($consentements);
    }

    // Enregistrer un compte rendu opératoire
    public function enregistrerCompteRenduOperatoire(Request $request, $idOperation)
    {
        $request->validate(['compte_rendu' => 'required|string']);
        $operation = Operation::findOrFail($idOperation);
        $operation->update(['compte_rendu' => $request->compte_rendu]);
        return response()->json($operation);
    }

    // Enregistrer une complication préopératoire
    public function enregistrerComplicationPreoperatoire(Request $request, $idOperation)
    {
        $request->validate(['description' => 'required|string']);
        $operation = Operation::findOrFail($idOperation);
        $operation->update(['complication' => $request->description]);
        return response()->json($operation);
    }

    // Prescrire des soins post-opératoires
    public function prescrireSoinPostOperatoire(Request $request, $idPatient)
    {
        $request->validate(['description' => 'required|string']);
        $traitement = Traitement::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => 'post-opératoire',
            'date' => now(),
            'description' => $request->description,
        ]);
        return response()->json($traitement);
    }

    // Mettre à jour l'évolution post-opératoire
    public function mettreAJourEvolutionPostOperatoire(Request $request, $idPatient)
    {
        $request->validate(['evolution' => 'required|string']);
        $suivi = SuiviHospitalier::create([
            'id' => \Str::uuid(),
            'id_dossier_admission' => DossierAdmissionSejour::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'evolution_clinique' => $request->evolution,
        ]);
        return response()->json($suivi);
    }

    // Demander un examen post-opératoire
    public function demanderExamenPostOperatoire(Request $request, $idPatient)
    {
        $request->validate(['type' => 'required|string']);
        $examen = ExamenImagerie::create([
            'id' => \Str::uuid(),
            'id_dossier_examen' => DossierExamenComplementaire::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => $request->type,
        ]);
        return response()->json($examen);
    }

    // Consulter un examen post-opératoire
    public function consulterExamenPostOperatoire($idPatient)
    {
        $examens = ExamenImagerie::whereHas('dossierExamen.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($examens);
    }
}
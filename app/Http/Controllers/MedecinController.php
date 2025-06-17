<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\Consentement;
use App\Models\Consultation;
use App\Models\DossierAdministratif;
use App\Models\ExamenImagerie;
use App\Models\Medicament;
use App\Models\Traitement;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Médecin
class MedecinController extends Controller
{
    // Rédiger un compte rendu de consultation
    public function redigerCompteRenduConsultation(Request $request, $idPatient)
    {
        $request->validate(['compte_rendu' => 'required|string']);
        $consultation = Consultation::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'compte_rendu' => $request->compte_rendu,
            'id_professionnel' => auth()->user()->professionnelSante->id,
        ]);
        return response()->json($consultation);
    }

    // Enregistrer un transfert de service
    public function enregistrerTransfertService(Request $request, $idPatient)
    {
        $request->validate(['id_service' => 'required|uuid']);
        $admission = Admission::create([
            'id' => \Str::uuid(),
            'id_dossier_admission' => DossierAdmissionSejour::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'id_service' => $request->id_service,
            'date' => now(),
        ]);
        return response()->json($admission);
    }

    // Interpréter un résultat d'examen
    public function interpreterResultatExamen(Request $request, $idExamen)
    {
        $request->validate(['interpretation' => 'required|string']);
        $examen = ExamenImagerie::findOrFail($idExamen);
        $examen->update(['resultat' => $request->interpretation]);
        return response()->json($examen);
    }

    // Enregistrer un diagnostic
    public function enregistrerDiagnostic(Request $request, $idPatient)
    {
        $request->validate(['diagnostic' => 'required|string']);
        $consultation = Consultation::create([
            'id' => \Str::uuid(),
            'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'diagnostic' => $request->diagnostic,
            'id_professionnel' => auth()->user()->professionnelSante->id,
        ]);
        return response()->json($consultation);
    }

    // Enregistrer une prescription
    public function enregistrerPrescription(Request $request, $idPatient)
    {
        $request->validate(['nom' => 'required|string', 'posologie' => 'string|nullable']);
        $medicament = Medicament::create([
            'id' => \Str::uuid(),
            'id_traitement' => Traitement::create([
                'id' => \Str::uuid(),
                'id_dossier_soins' => DossierSoinsMedicaux::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
                'type' => 'médicament',
                'date' => now(),
            ])->id,
            'nom' => $request->nom,
            'posologie' => $request->posologie,
        ]);
        return response()->json($medicament);
    }

    // Consulter les traitements en cours
    public function consulterTraitementsEnCours($idPatient)
    {
        $traitements = Traitement::whereHas('dossierSoins.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($traitements);
    }

    // Consulter les antécédents médicaux
    public function consulterAntecedentsMedicaux($idPatient)
    {
        $antecedents = Antecedent::where('id_patient', $idPatient)->get();
        return response()->json($antecedents);
    }

    // Consulter les résultats d'examens
    public function consulterResultatsExamens($idPatient)
    {
        $resultats = ExamenImagerie::whereHas('dossierExamen.dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($resultats);
    }

    // Mettre à jour les antécédents médicaux
    public function mettreAJourAntecedentsMedicaux(Request $request, $idPatient)
    {
        $request->validate(['maladie' => 'required|string']);
        $antecedent = Antecedent::create([
            'id' => \Str::uuid(),
            'id_patient' => $idPatient,
            'maladie' => $request->maladie,
        ]);
        return response()->json($antecedent);
    }

    // Consulter les données administratives
    public function consulterDonneesAdministratives($idPatient)
    {
        $donnees = DossierAdministratif::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->get();
        return response()->json($donnees);
    }

    // Demander un examen complémentaire
    public function demanderExamenComplementaire(Request $request, $idPatient)
    {
        $request->validate(['type' => 'required|string']);
        $examen = ExamenImagerie::create([
            'id' => \Str::uuid(),
            'id_dossier_examen' => DossierExamenComplementaire::whereHas('dossier.patient', fn($q) => $q->where('id', $idPatient))->first()->id,
            'type' => $request->type,
        ]);
        return response()->json($examen);
    }
}
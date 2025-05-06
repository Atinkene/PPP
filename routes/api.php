<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\AnesthesisteController;
use App\Http\Controllers\AssistantMedicalController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\BiologisteController;
use App\Http\Controllers\ChirurgienController;
use App\Http\Controllers\InfirmierController;
use App\Http\Controllers\KinesitherapeuteController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\PersonnelAdministratifController;
use App\Http\Controllers\RadiologueController;
use App\Http\Controllers\PsychologueController;
use Illuminate\Support\Facades\Route;

// Routes pour l'authentification
Route::post('/inscription', [InscriptionController::class, 'inscrire']);
Route::post('/connexion', [AuthController::class, 'connecter']);
Route::post('/deconnexion', [AuthController::class, 'deconnecter'])->middleware('auth:sanctum');

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Fonctionnalités communes à tous les utilisateurs
    Route::get('/donnees-personnelles', [UtilisateurController::class, 'consulterDonneesPersonnelles']);
    Route::put('/donnees-personnelles', [UtilisateurController::class, 'modifierDonneesPersonnelles']);
    Route::get('/rendez-vous', [UtilisateurController::class, 'consulterRendezVous']);

    // Routes pour Anesthésiste
    Route::middleware('role:anesthesiste')->group(function () {
        Route::get('/patients/{idPatient}/antecedents', [AnesthesisteController::class, 'consulterAntecedents']);
        Route::get('/patients/{idPatient}/examens-preoperatoires', [AnesthesisteController::class, 'consulterExamensPreoperatoires']);
        Route::post('/patients/{idPatient}/evaluation-anesthesique', [AnesthesisteController::class, 'enregistrerEvaluationAnesthesique']);
        Route::post('/operations/{idOperation}/parametres-intervention', [AnesthesisteController::class, 'enregistrerParametresIntervention']);
        Route::post('/operations/{idOperation}/compte-rendu-anesthesique', [AnesthesisteController::class, 'enregistrerCompteRenduAnesthesique']);
        Route::post('/patients/{idPatient}/soin-post-anesthesique', [AnesthesisteController::class, 'prescrireSoinPostAnesthesique']);
    });

    // Routes pour Assistant Médical
    Route::middleware('role:assistant_medical')->group(function () {
        Route::post('/rendez-vous', [AssistantMedicalController::class, 'planifierRendezVous']);
        Route::get('/rendez-vous-planifies', [AssistantMedicalController::class, 'consulterRendezVousPlanifies']);
        Route::put('/rendez-vous/{id}/annuler', [AssistantMedicalController::class, 'annulerRendezVous']);
        Route::get('/demandes-rendez-vous', [AssistantMedicalController::class, 'consulterDemandesRendezVous']);
    });

    // Routes pour Patient
    Route::middleware('role:patient')->group(function () {
        Route::post('/rendez-vous', [PatientController::class, 'prendreRendezVous']);
        Route::put('/rendez-vous/{id}/annuler', [PatientController::class, 'annulerRendezVous']);
        Route::get('/donnees-personnelles', [PatientController::class, 'consulterDonneesPersonnelles']);
        Route::get('/antecedents-medicaux', [PatientController::class, 'consulterAntecedentsMedicaux']);
        Route::get('/ordonnances', [PatientController::class, 'consulterOrdonnances']);
        Route::put('/donnees-personnelles', [PatientController::class, 'modifierDonneesPersonnelles']);
        Route::post('/antecedents-medicaux', [PatientController::class, 'fournirAntecedentsMedicaux']);
        Route::get('/instructions-sortie', [PatientController::class, 'consulterInstructionsSortie']);
        Route::get('/resultats-examens', [PatientController::class, 'consulterResultatsExamens']);
        Route::post('/consentements', [PatientController::class, 'enregistrerConsentement']);
        Route::get('/copie-dossier-medical', [PatientController::class, 'demanderCopieDossier']);
        Route::post('/signaler-erreur', [PatientController::class, 'signalerErreur']);
        Route::post('/evaluer-prise-en-charge', [PatientController::class, 'evaluerPriseEnCharge']);
    });

    // Routes pour Biologiste
    Route::middleware('role:biologiste')->group(function () {
        Route::get('/demandes-examen', [BiologisteController::class, 'consulterDemandesExamen']);
        Route::get('/patients/{idPatient}/antecedents', [BiologisteController::class, 'consulterAntecedents']);
        Route::post('/examens/{idExamen}/resultat-biologique', [BiologisteController::class, 'enregistrerResultatBiologique']);
        Route::post('/examens/{idExamen}/signaler-anomalie', [BiologisteController::class, 'signalerAnomalieUrgente']);
        Route::put('/examens/{idExamen}/donnees-biologiques', [BiologisteController::class, 'mettreAJourDonneesBiologiques']);
    });

    // Routes pour Chirurgien
    Route::middleware('role:chirurgien')->group(function () {
        Route::get('/patients/{idPatient}/antecedents', [ChirurgienController::class, 'consulterAntecedents']);
        Route::get('/patients/{idPatient}/examens-preoperatoires', [ChirurgienController::class, 'consulterExamensPreoperatoires']);
        Route::post('/patients/{idPatient}/fiche-preoperatoire', [ChirurgienController::class, 'enregistrerFichePreoperatoire']);
        Route::get('/patients/{idPatient}/consentements', [ChirurgienController::class, 'consulterConsentements']);
        Route::post('/operations/{idOperation}/compte-rendu-operatoire', [ChirurgienController::class, 'enregistrerCompteRenduOperatoire']);
        Route::post('/operations/{idOperation}/complication-preoperatoire', [ChirurgienController::class, 'enregistrerComplicationPreoperatoire']);
        Route::post('/patients/{idPatient}/soin-post-operatoire', [ChirurgienController::class, 'prescrireSoinPostOperatoire']);
        Route::post('/patients/{idPatient}/evolution-post-operatoire', [ChirurgienController::class, 'mettreAJourEvolutionPostOperatoire']);
        Route::post('/patients/{idPatient}/examen-post-operatoire', [ChirurgienController::class, 'demanderExamenPostOperatoire']);
        Route::get('/patients/{idPatient}/examen-post-operatoire', [ChirurgienController::class, 'consulterExamenPostOperatoire']);
    });

    // Routes pour Infirmier
    Route::middleware('role:infirmier')->group(function () {
        Route::post('/dossiers-admission/{idDossierAdmission}/suivi-hospitalier', [InfirmierController::class, 'mettreAJourSuiviHospitalier']);
        Route::put('/rendez-vous/{id}/annuler', [InfirmierController::class, 'annulerRendezVous']);
        Route::get('/rendez-vous', [InfirmierController::class, 'consulterRendezVous']);
        Route::get('/patients/{idPatient}/prescriptions', [InfirmierController::class, 'consulterPrescriptions']);
        Route::get('/patients/{idPatient}/antecedents', [InfirmierController::class, 'consulterAntecedents']);
        Route::post('/patients/{idPatient}/signes-vitaux', [InfirmierController::class, 'enregistrerSignesVitaux']);
        Route::post('/patients/{idPatient}/etat-general', [InfirmierController::class, 'enregistrerEtatGeneral']);
        Route::post('/patients/{idPatient}/douleur', [InfirmierController::class, 'enregistrerDouleur']);
        Route::post('/medicaments/{idMedicament}/administration', [InfirmierController::class, 'enregistrerAdministrationMedicament']);
        Route::post('/patients/{idPatient}/soin-realise', [InfirmierController::class, 'enregistrerSoinRealise']);
        Route::post('/patients/{idPatient}/signaler-complication', [InfirmierController::class, 'signalerComplication']);
    });

    // Routes pour Kinésithérapeute
    Route::middleware('role:kinesitherapeute')->group(function () {
        Route::get('/patients/{idPatient}/prescriptions', [KinesitherapeuteController::class, 'consulterPrescriptions']);
        Route::get('/patients/{idPatient}/etat-post-operatoire', [KinesitherapeuteController::class, 'consulterEtatPostOperatoire']);
        Route::post('/patients/{idPatient}/seance-reeducation', [KinesitherapeuteController::class, 'enregistrerSeanceReeducation']);
        Route::post('/patients/{idPatient}/progres', [KinesitherapeuteController::class, 'enregistrerProgres']);
        Route::post('/patients/{idPatient}/recommander-suivi', [KinesitherapeuteController::class, 'recommanderSuivi']);
    });

    // Routes pour Médecin
    Route::middleware('role:medecin')->group(function () {
        Route::post('/patients/{idPatient}/compte-rendu-consultation', [MedecinController::class, 'redigerCompteRenduConsultation']);
        Route::post('/patients/{idPatient}/transfert-service', [MedecinController::class, 'enregistrerTransfertService']);
        Route::post('/examens/{idExamen}/interpretation', [MedecinController::class, 'interpreterResultatExamen']);
        Route::post('/patients/{idPatient}/diagnostic', [MedecinController::class, 'enregistrerDiagnostic']);
        Route::post('/patients/{idPatient}/prescription', [MedecinController::class, 'enregistrerPrescription']);
        Route::get('/patients/{idPatient}/traitements', [MedecinController::class, 'consulterTraitementsEnCours']);
        Route::get('/patients/{idPatient}/antecedents-medicaux', [MedecinController::class, 'consulterAntecedentsMedicaux']);
        Route::get('/patients/{idPatient}/resultats-examens', [MedecinController::class, 'consulterResultatsExamens']);
        Route::post('/patients/{idPatient}/antecedents-medicaux', [MedecinController::class, 'mettreAJourAntecedentsMedicaux']);
        Route::get('/patients/{idPatient}/donnees-administratives', [MedecinController::class, 'consulterDonneesAdministratives']);
        Route::post('/patients/{idPatient}/examen-complementaire', [MedecinController::class, 'demanderExamenComplementaire']);
    });

    // Routes pour Personnel Administratif
    Route::middleware('role:personnel_administratif')->group(function () {
        Route::put('/admissions/{idAdmission}/service-destination', [PersonnelAdministratifController::class, 'mettreAJourServiceDestination']);
        Route::post('/donnees-personnelles', [PersonnelAdministratifController::class, 'enregistrerDonneesPersonnelles']);
        Route::post('/donnees-assurance', [PersonnelAdministratifController::class, 'enregistrerDonneesAssurance']);
        Route::get('/patients/{idPatient}/donnees-administratives', [PersonnelAdministratifController::class, 'consulterDonneesAdministratives']);
        Route::post('/patients/{idPatient}/admission', [PersonnelAdministratifController::class, 'planifierAdmission']);
        Route::put('/assurances/{idAssurance}/donnees-financieres', [PersonnelAdministratifController::class, 'mettreAJourDonneesFinancieres']);
        Route::post('/dossier-patient', [PersonnelAdministratifController::class, 'creerDossierPatient']);
        Route::post('/patients/{idPatient}/sortie', [PersonnelAdministratifController::class, 'planifierSortie']);
    });

    // Routes pour Radiologue
    Route::middleware('role:radiologue')->group(function () {
        Route::get('/demandes-examen', [RadiologueController::class, 'consulterDemandesExamen']);
        Route::get('/patients/{idPatient}/antecedents', [RadiologueController::class, 'consulterAntecedents']);
        Route::post('/examens/{idExamen}/resultat-imagerie', [RadiologueController::class, 'enregistrerResultatImagerie']);
        Route::post('/examens/{idExamen}/signaler-anomalie', [RadiologueController::class, 'signalerAnomalieUrgente']);
        Route::put('/examens/{idExamen}/donnees-imagerie', [RadiologueController::class, 'mettreAJourDonneesImagerie']);
    });

    // Routes pour Psychologue
    Route::middleware('role:psychologue')->group(function () {
        Route::get('/patients/{idPatient}/antecedents-medicaux', [PsychologueController::class, 'consulterAntecedentsMedicaux']);
        Route::get('/patients/{idPatient}/donnees-sociales', [PsychologueController::class, 'consulterDonneesSociales']);
        Route::post('/patients/{idPatient}/diagnostic-psychologique', [PsychologueController::class, 'enregistrerDiagnosticPsychologique']);
        Route::post('/patients/{idPatient}/prescription-psychologique', [PsychologueController::class, 'enregistrerPrescriptionPsychologique']);
        Route::post('/patients/{idPatient}/compte-rendu-seance', [PsychologueController::class, 'redigerCompteRenduSeance']);
        Route::post('/patients/{idPatient}/note-sociale', [PsychologueController::class, 'ajouterNoteSociale']);
    });
});
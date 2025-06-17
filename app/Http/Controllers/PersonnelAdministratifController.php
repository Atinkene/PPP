<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Assurance;
use App\Models\CompteRenduSortie;
use App\Models\Contact;
use App\Models\DossierAdministratif;
use App\Models\DossierAdmissionSejour;
use App\Models\DossierPatient;
use App\Models\DossierSortieSuivi;
use App\Models\ProfessionnelSante;
use App\Models\ServiceHospitalier;
use App\Models\Consentement;
use App\Models\Antecedent;
use App\Models\ExamenImagerie;  
use App\Models\Medicament;
use App\Models\RendezVous;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class PersonnelAdministratifController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Search patients by numero, nom, prenom, or date_naissance (exact matches).
     * GET /patients/search
     */
    public function searchPatients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $query = Patient::with('user');

        if ($request->filled('numero')) {
            $query->where('id', $request->numero);
        }

        if ($request->filled('nom')) {
            $query->whereHas('user', fn($q) => $q->where('nom', $request->nom));
        }

        if ($request->filled('prenom')) {
            $query->whereHas('user', fn($q) => $q->where('prenom', $request->prenom));
        }

        if ($request->filled('date_naissance')) {
            $query->whereHas('user', fn($q) => $q->whereDate('date_naissance', $request->date_naissance));
        }

        $patients = $query->get()->map(fn($patient) => [
            'id' => $patient->id,
            'numero' => $patient->id,
            'nom' => $patient->user->nom,
            'prenom' => $patient->user->prenom,
            'email' => $patient->user->email,
            'date_naissance' => $patient->user->date_naissance?->format('Y-m-d'),
            'groupe_sanguin' => $patient->groupe_sanguin,
        ]);

        return response()->json($patients, Response::HTTP_OK);
    }

    /**
     * Get patient details and accessible dossiers.
     * GET /patients/{id}/details
     */
    public function getPatientDetails($id)
    {
        try {
            $patient = Patient::with(['user', 'dossiersPatients'])->findOrFail($id);

            return response()->json([
                'personal_info' => [
                    'id' => $patient->user->id,
                    'email' => $patient->user->email,
                    'nom' => $patient->user->nom,
                    'prenom' => $patient->user->prenom,
                    'sexe' => $patient->user->sexe,
                    'date_naissance' => $patient->user->date_naissance?->format('Y-m-d'),
                    'cin' => $patient->user->cin,
                    'lieu_naissance' => $patient->user->lieu_naissance,
                    'nationalite' => $patient->user->nationalite,
                    'adresse_postale' => $patient->user->adresse_postale,
                    'numero_telephone' => $patient->user->numero_telephone,
                    'groupe_sanguin' => $patient->groupe_sanguin,
                ],
                'dossiers' => $patient->dossiersPatients->map(fn($dossier) => [
                    'id' => $dossier->id,
                    'created_at' => $dossier->created_at->format('Y-m-d H:i:s'),
                    'etablissement_id' => $dossier->id_etablissement,
                ])->values(),
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Patient not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update patient personal information.
     * PUT /patients/{id}/donnees-personnelles
     */
    public function updatePersonalInfo(Request $request, $id)
    {
        try {
            $patient = Patient::with('user')->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email|unique:users,email,' . $patient->user->id,
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'sexe' => 'nullable|string|in:M,F,Autre|max:10',
                'date_naissance' => 'nullable|date',
                'cin' => 'nullable|string|unique:users,cin,' . $patient->user->id,
                'lieu_naissance' => 'nullable|string|max:255',
                'nationalite' => 'nullable|string|max:255',
                'adresse_postale' => 'nullable|string',
                'numero_telephone' => 'nullable|string|unique:users,numero_telephone,' . $patient->user->id,
                'groupe_sanguin' => 'nullable|string|max:10',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }

            $userData = array_filter([
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
            ], fn($value) => !is_null($value));

            $patientData = array_filter([
                'groupe_sanguin' => $request->groupe_sanguin,
            ], fn($value) => !is_null($value));

            if ($userData) {
                $patient->user->update($userData);
            }

            if ($patientData) {
                $patient->update($patientData);
            }

            return response()->json([
                'message' => 'Personal info updated',
                'personal_info' => [
                    'id' => $patient->user->id,
                    'email' => $patient->user->email,
                    'nom' => $patient->user->nom,
                    'prenom' => $patient->user->prenom,
                    'sexe' => $patient->user->sexe,
                    'date_naissance' => $patient->user->date_naissance?->format('Y-m-d'),
                    'cin' => $patient->user->cin,
                    'lieu_naissance' => $patient->user->lieu_naissance,
                    'nationalite' => $patient->user->nationalite,
                    'adresse_postale' => $patient->user->adresse_postale,
                    'numero_telephone' => $patient->user->numero_telephone,
                    'groupe_sanguin' => $patient->groupe_sanguin,
                ]
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Patient not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get sub-elements of a dossier.
     * GET /dossiers/{id}/sub-elements
     */

    
    public function getDossierSubElements($id)
    {
        try {
            $dossier = DossierPatient::with([
                'dossierAdmissionSejour.admissions.service',
                'dossierSoinsMedicaux.consultations.professionnel',
                'dossierAdministratif.contact',
                'dossierAdministratif.assurances',
                'dossierAdministratif.consentements',
                'dossierSortieSuivi.comptesRendusSortie',
            ])->findOrFail($id);

            return response()->json([
                'dossier_id' => $dossier->id,
                'patient_id' => $dossier->id_patient,
                'sub_elements' => [
                    'admissions' => $dossier->dossierAdmissionSejour && $dossier->dossierAdmissionSejour->admissions
                        ? $dossier->dossierAdmissionSejour->admissions->map(function ($admission) {
                            return [
                                'id' => $admission->id,
                                'motif' => $admission->motif,
                                'date' => $admission->date ?? null,
                                'service' => $admission->service ? [
                                    'id' => $admission->service->id,
                                    'nom' => $admission->service->nom,
                                ] : null,
                            ];
                        })->values()
                        : [],
                    'consultations' => $dossier->dossierSoinsMedicaux && $dossier->dossierSoinsMedicaux->consultations
                        ? $dossier->dossierSoinsMedicaux->consultations->map(function ($consultation) {
                            return [
                                'id' => $consultation->id,
                                'compte_rendu' => $consultation->compte_rendu,
                                'examen_clinique' => $consultation->examen_clinique,
                                'diagnostic' => $consultation->diagnostic,
                                'professionnel' => $consultation->professionnel ? [
                                    'id' => $consultation->professionnel->id,
                                    'type' => $consultation->professionnel->type,
                                ] : null,
                            ];
                        })->values()
                        : [],
                    'administratif' => $dossier->dossierAdministratif ? [
                        'contact' => $dossier->dossierAdministratif->contact
                            ? [
                                'id' => $dossier->dossierAdministratif->contact->id,
                                'adresse_postale' => $dossier->dossierAdministratif->contact->adresse_postale,
                                'numero_telephone' => $dossier->dossierAdministratif->contact->numero_telephone,
                                'email' => $dossier->dossierAdministratif->contact->email,
                            ]
                            : null,
                        'assurances' => $dossier->dossierAdministratif->assurances
                            ? $dossier->dossierAdministratif->assurances->map(function ($assurance) {
                                return [
                                    'id' => $assurance->id,
                                    'numero_securite_social' => $assurance->numero_securite_social,
                                    'organisme' => $assurance->organisme_assurance_sante,
                                    'prise_en_charge' => $assurance->prise_en_charge,
                                ];
                            })->values() : [],
                        'consentements' => $dossier->dossierAdministratif->consentements
                            ? $dossier->dossierAdministratif->consentements->map(function ($consentement) {
                                return [
                                    'id' => $consentement->id,
                                    'type' => $consentement->type,
                                    'statut' => $consentement->statut,
                                    'date_autorisation' => $consentement->date_autorisation ?? null,
                                ];
                            })->values() : [],
                    ] : [],
                    'sorties' => $dossier->dossierSortieSuivi && $dossier->dossierSortieSuivi->comptesRendusSortie
                        ? $dossier->dossierSortieSuivi->comptesRendusSortie->map(function ($sortie) {
                            return [
                                'id' => $sortie->id,
                                'date' => $sortie->date ?? null,
                            ];
                        })->values()
                        : [],
                ]
            ], \Illuminate\Http\Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Dossier not found'], \Illuminate\Http\Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], \Illuminate\Http\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update service destination for an admission.
     * PUT /admissions/{idAdmission}/service-destination
     */
    public function mettreAJourServiceDestination(Request $request, $idAdmission)
    {
        $validator = Validator::make($request->all(), [
            'id_service' => 'required|uuid|exists:services_hospitaliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $admission = Admission::findOrFail($idAdmission);
            $admission->update(['id_service' => $request->id_service]);
            return response()->json($admission, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Admission not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Register personal information for a new user.
     * POST /donnees-personnelles
     */
    public function enregistrerDonneesPersonnelles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|string|in:M,F,Autre',
            'date_naissance' => 'required|date',
            'cin' => 'required|string|unique:users,cin',
            'lieu_naissance' => 'required|string|max:255',
            'nationalite' => 'required|string|max:255',
            'adresse_postale' => 'nullable|string',
            'numero_telephone' => 'nullable|string|unique:users,numero_telephone',
            'groupe_sanguin' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = User::create([
                'id' => Str::uuid()->toString(),
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
                'password' => bcrypt('default_password'), // TODO: Implement proper password handling
                'role' => 'patient',
            ]);

            $patient = Patient::create([
                'id' => Str::uuid()->toString(),
                'id_user' => $user->id,
                'groupe_sanguin' => $request->groupe_sanguin,
            ]);

            return response()->json(['user' => $user, 'patient' => $patient], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Register insurance data.
     * POST /donnees-assurance
     */
    public function enregistrerDonneesAssurance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_dossier_admin' => 'required|uuid|exists:dossiers_administratifs,id',
            'numero_securite_social' => 'nullable|string|max:255',
            'organisme_assurance_sante' => 'nullable|string|max:255',
            'prise_en_charge' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $assurance = Assurance::create([
                'id' => Str::uuid()->toString(),
                'id_dossier_admin' => $request->id_dossier_admin,
                'numero_securite_social' => $request->numero_securite_social,
                'organisme_assurance_sante' => $request->organisme_assurance_sante,
                'prise_en_charge' => $request->prise_en_charge,
            ]);

            return response()->json($assurance, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Consult administrative data for a patient.
     * GET /patients/{idPatient}/donnees-administratives
     */
    public function consulterDonneesAdministratives($idPatient)
    {
        try {
            $dossier = DossierPatient::with([
                'administratif.',
                'administratif.assurances',
                'administratif.consentements',
            ])->whereHas('patient', fn($q) => $q->where('id', $idPatient))->firstOrFail();

            return response()->json($dossier->administratif, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Dossier or patient not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Plan an admission for a patient.
     * POST /patients/{idPatient}/admission
     */
    public function planifierAdmission(Request $request, $idPatient)
    {
        $validator = Validator::make($request->all(), [
            'motif' => 'nullable|string|max:255',
            'date' => 'required|date',
            'id_service' => 'nullable|uuid|exists:services_hospitaliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $dossier = DossierPatient::whereHas('patient', fn($q) => $q->where('id', $idPatient))->firstOrFail();
            $dossierAdmission = $dossier->admissionSejour()->firstOrCreate([
                'id' => Str::uuid()->toString(),
                'id_dossier' => $dossier->id,
            ]);

            $admission = Admission::create([
                'id' => Str::uuid()->toString(),
                'id_dossier_admission' => $dossierAdmission->id,
                'motif' => $request->motif,
                'date' => $request->date,
                'id_service' => $request->id_service,
            ]);

            return response()->json($admission, Response::HTTP_CREATED);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Patient or dossier not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update financial data for an insurance.
     * PUT /assurances/{idAssurance}/donnees-financieres
     */
    public function mettreAJourDonneesFinancieres(Request $request, $idAssurance)
    {
        $validator = Validator::make($request->all(), [
            'prise_en_charge' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $assurance = Assurance::findOrFail($idAssurance);
            $assurance->update(['prise_en_charge' => $request->prise_en_charge]);
            return response()->json($assurance, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Assurance not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a patient dossier.
     * POST /dossier-patient
     */
    public function creerDossierPatient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'login' => 'required|string|unique:users,login',
            'password' => 'required|string|min:6',
            'sexe' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'cin' => 'nullable|string',
            'lieu_naissance' => 'nullable|string',
            'nationalite' => 'nullable|string',
            'adresse_postale' => 'nullable|string',
            'numero_telephone' => 'nullable|string',
            'groupe_sanguin' => 'nullable|string',
            'id_etablissement' => 'nullable|uuid|exists:etablissements,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        try {
            // 1. Créer l'utilisateur
            $user = User::create([
                'id' => Str::uuid()->toString(),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'login' => $request->login,
                'password' => bcrypt($request->password),
                'sexe' => $request->sexe,
                'date_naissance' => $request->date_naissance,
                'cin' => $request->cin,
                'lieu_naissance' => $request->lieu_naissance,
                'nationalite' => $request->nationalite,
                'adresse_postale' => $request->adresse_postale,
                'numero_telephone' => $request->numero_telephone,
                'role' => 'patient',
            ]);

            // 2. Créer le patient
            $patient = Patient::create([
                'id' => Str::uuid()->toString(),
                'id_user' => $user->id,
                'groupe_sanguin' => $request->groupe_sanguin,
            ]);

            // 3. Créer le dossier patient
            $dossier = DossierPatient::create([
                'id' => Str::uuid()->toString(),
                'id_patient' => $patient->id,
                'id_etablissement' => $request->id_etablissement,
            ]);

            // 4. Créer tous les sous-dossiers liés au dossier patient
            $dossier->dossierAdministratif()->create(['id' => Str::uuid()->toString(),'id_dossier' => $dossier->id]);
            $dossier->dossierAdmissionSejour()->create(['id' => Str::uuid()->toString(),'id_dossier' => $dossier->id]);
            $dossier->dossierSoinsMedicaux()->create(['id' => Str::uuid()->toString(), 'id_dossier' => $dossier->id]);
            $dossier->dossierSortieSuivi()->create(['id' => Str::uuid()->toString(), 'id_dossier' => $dossier->id]);
            $dossier->dossierExamensComplementaires()->create(['id' => Str::uuid()->toString(), 'id_dossier' => $dossier->id]);
            $dossier->dossierSoinsInfirmiers()->create(['id' => Str::uuid()->toString(), 'id_dossier' => $dossier->id]);
            $dossier->dossierChirurgieAnesthesie()->create(['id' => Str::uuid()->toString(), 'id_dossier' => $dossier->id]);
            $dossier->dossierPsychoSociaux()->create(['id' => Str::uuid()->toString(), 'id_dossier' => $dossier->id]);

            return response()->json([
                'user' => $user,
                'patient' => $patient,
                'dossier' => $dossier->load([
                    'dossierAdministratif',
                    'dossierAdmissionSejour',
                    'dossierSoinsMedicaux',
                    'dossierSortieSuivi',
                    'dossierExamensComplementaires',
                    'dossierSoinsInfirmiers',
                    'dossierChirurgieAnesthesie',
                    'dossierPsychoSociaux',
                ])
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Plan a patient discharge.
     * POST /patients/{idPatient}/sortie
     */
    public function planifierSortie(Request $request, $idPatient)
    {
        if ($request->has('date')) {
            try {
                $date = Carbon::createFromFormat('m/d/Y', $request->input('date'));
            $request->merge([
                'date' => $date->format('Y-m-d H:i:s')
            ]);
            } catch (\Exception $e) {
                // Si la date n'est pas parseable, on laisse le validator gérer l'erreur
            }
        }
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $dossier = DossierPatient::whereHas('patient', fn($q) => $q->where('id', $idPatient))->firstOrFail();
            $dossierSortie = $dossier->sortieSuivi()->firstOrCreate([
                'id' => Str::uuid()->toString(),
                'id_dossier' => $dossier->id,
            ]);

            $compteRendu = CompteRenduSortie::create([
                'id' => Str::uuid()->toString(),
                'id_dossier_sortie' => $dossierSortie->id,
                'date' => $request->date,
            ]);

            return response()->json($compteRendu, Response::HTTP_CREATED);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Patient or dossier not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

// $dossiers = DossierPatient::with([
//         'patient',
//         'etablissement',
//         'dossierAdministratif.contact',
//         'dossierAdministratif.assurances',
//         'dossierAdministratif.consentements',
//         'dossierAdministratif.Urgences',
//         'dossierSoinsMedicaux.antecedents.allergies',
//         'dossierSoinsMedicaux.antecedents.vaccinations',
//         'dossierSoinsMedicaux.antecedents.operations',
//         'dossierSoinsMedicaux.antecedents.traitementsLongueDuree',
//         'dossierSoinsMedicaux.antecedents.maladiesChroniques',
//         'dossierSoinsMedicaux.consultations.symptomes',
//         'dossierSoinsMedicaux.traitements.medicaments.administrationsMedicaments',
//         'dossierSoinsMedicaux.traitements.effetsSecondaires',
//         'dossierAdmissionSejour.admissions',
//         'dossierAdmissionSejour.comptesRendusHospitalisation',
//         'dossierAdmissionSejour.suivisHospitaliers',
//         'dossierSortieSuivi.comptesRendusSortie',
//         'dossierExamensComplementaires.examensImagerie.imagesDicom',
//         'dossierChirurgieAnesthesie.fichesPreoperatoires',
//         'dossierPsychoSociaux.evaluationsPsychologiques',
//         'dossierSoinsInfirmiers.administrationsMedicaments',
//         'dossierSoinsInfirmiers.observationsInfirmieres',
//         'rendezVous'
//     ])
//     ->whereHas('patient', fn($q) => $q->where('id', $idPatient))
//     ->get();
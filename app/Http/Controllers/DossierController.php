<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\DossierPatient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class DossierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Search patients by numero, nom, prenom, or date_naissance.
     * GET /patients/search
     */
    public function searchPatients(Request $request)
    {
        try {
            // Validate query parameters
            $validator = Validator::make($request->all(), [
                'numero' => 'nullable|string|max:255',
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'date_naissance' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Données de recherche invalides',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check authorization
            if (!$this->canSearchPatients()) {
                return response()->json([
                    'message' => 'Accès non autorisé à la recherche de patients'
                ], Response::HTTP_FORBIDDEN);
            }

            // Build query
            $query = Patient::with('user');

            if ($request->has('numero')) {
                $query->where('id', 'like', '%' . $request->numero . '%');
            }

            if ($request->has('nom')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('nom', 'like', '%' . $request->nom . '%');
                });
            }

            if ($request->has('prenom')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('prenom', 'like', '%' . $request->prenom . '%');
                });
            }

            if ($request->has('date_naissance')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->whereDate('date_naissance', $request->date_naissance);
                });
            }

            $patients = $query->get()->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'numero' => $patient->id,
                    'nom' => $patient->user->nom,
                    'prenom' => $patient->user->prenom,
                    'email' => $patient->user->email,
                    'date_naissance' => $patient->user->date_naissance ?? null, // Return string or null
                    'groupe_sanguin' => $patient->groupe_sanguin,
                ];
            });

            return response()->json($patients, Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve dossiers for a patient.
     * GET /patients/{id}/dossier
     */
    public function getPatientDossier($id)
    {
        try {
            $patient = Patient::with(['dossiersPatients', 'user', 'antecedents', 'contactsUrgence', 'rendezVous'])
                ->findOrFail($id);

            if (!$this->canAccessPatientDossier($patient)) {
                return response()->json([
                    'message' => 'Accès non autorisé au dossier du patient'
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'patient' => [
                    'id' => $patient->id,
                    'nom' => $patient->user->nom,
                    'prenom' => $patient->user->prenom,
                    'email' => $patient->user->email,
                    'groupe_sanguin' => $patient->groupe_sanguin,
                ],
                'dossiers' => $patient->dossiersPatients->map(function ($dossier) {
                    return [
                        'id' => $dossier->id,
                        'type' => $dossier->type ?? 'N/A',
                        'description' => $dossier->description ?? '',
                        'created_at' => $dossier->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'antecedents' => $patient->antecedents->map(function ($antecedent) {
                    return [
                        'id' => $antecedent->id,
                        'type' => $antecedent->type,
                        'description' => $antecedent->description,
                    ];
                }),
                'contacts_urgence' => $patient->contactsUrgence,
                'rendez_vous' => $patient->rendezVous,
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Patient non trouvé'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new dossier for a patient.
     * POST /dossier-patient
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_patient' => 'required|exists:patients,id',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $patient = Patient::findOrFail($request->id_patient);

            if (!$this->canManageDossier($patient)) {
                return response()->json([
                    'message' => 'Non autorisé à créer un dossier'
                ], Response::HTTP_FORBIDDEN);
            }

            $dossier = DossierPatient::create([
                'id_patient' => $patient->id,
                'type' => $request->type,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Dossier créé avec succès',
                'dossier' => [
                    'id' => $dossier->id,
                    'type' => $dossier->type,
                    'description' => $dossier->description,
                    'created_at' => $dossier->created_at->format('Y-m-d H:i:s'),
                ]
            ], Response::HTTP_CREATED);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Patient non trouvé'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing dossier.
     * PUT /dossier-patient/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $dossier = DossierPatient::findOrFail($id);
            $patient = $dossier->patient;

            if (!$this->canManageDossier($patient)) {
                return response()->json([
                    'message' => 'Non autorisé à modifier ce dossier'
                ], Response::HTTP_FORBIDDEN);
            }

            $dossier->update([
                'type' => $request->type ?? $dossier->type,
                'description' => $request->description ?? $dossier->description,
            ]);

            return response()->json([
                'message' => 'Dossier mis à jour avec succès',
                'dossier' => [
                    'id' => $dossier->id,
                    'type' => $dossier->type,
                    'description' => $dossier->description,
                    'created_at' => $dossier->created_at->format('Y-m-d H:i:s'),
                ]
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dossier non trouvé'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a dossier.
     * DELETE /dossier-patient/{id}
     */
    public function destroy($id)
    {
        try {
            $dossier = DossierPatient::findOrFail($id);
            $patient = $dossier->patient;

            if (!$this->canManageDossier($patient)) {
                return response()->json([
                    'message' => 'Non autorisé à supprimer ce dossier'
                ], Response::HTTP_FORBIDDEN);
            }

            $dossier->delete();

            return response()->json([
                'message' => 'Dossier supprimé avec succès'
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Dossier non trouvé'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if user can access patient dossier (role-based).
     */
    private function canAccessPatientDossier(Patient $patient)
    {
        $user = Auth::user();
        $role = $user->role;

        if ($role === 'patient' && $patient->id_user === $user->id) {
            return true;
        }

        $allowedRoles = [
            'medecin',
            'anesthesiste',
            'chirurgien',
            'infirmier',
            'biologiste',
            'radiologue',
            'psychologue',
            'personnel_administratif',
            'assistant_medical',
            'kinesitherapeute',
        ];

        return in_array($role, $allowedRoles);
    }

    /**
     * Check if user can manage (create/update/delete) dossier.
     */
    private function canManageDossier(Patient $patient)
    {
        $user = Auth::user();
        $role = $user->role;

        $allowedRoles = ['personnel_administratif', 'medecin'];

        return in_array($role, $allowedRoles);
    }

    /**
     * Check if user can search patients.
     */
    private function canSearchPatients()
    {
        $user = Auth::user();
        $role = $user->role;

        $allowedRoles = [
            'medecin',
            'anesthesiste',
            'chirurgien',
            'infirmier',
            'biologiste',
            'radiologue',
            'psychologue',
            'personnel_administratif',
            'assistant_medical',
            'kinesitherapeute',
        ];

        return in_array($role, $allowedRoles);
    }
}

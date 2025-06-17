<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\ExamenImagerie;

class OrthancController extends Controller
{
    protected $orthancUrl;
    protected $orthancUsername;
    protected $orthancPassword;

    public function __construct()
    {
        $this->orthancUrl = env('ORTHANC_URL', 'http://localhost:8042');
        $this->orthancUsername = env('ORTHANC_USERNAME', 'alice');
        $this->orthancPassword = env('ORTHANC_PASSWORD', 'alicePassword');
    }

    /**
     * Récupère les études d'un patient depuis Orthanc
     */
    public function getPatientStudies(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Vérification des rôles autorisés
        $allowedRoles = ['radiologue', 'medecin', 'patient'];
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Récupération de l'ID patient depuis la requête
        $patientId = $request->input('patient_id');
        
        // Si l'utilisateur est un patient, utiliser son propre ID
        if ($user->role === 'patient') {
            $patientId = $user->patient_id ?? $patientId;
        }

        if (!$patientId) {
            return response()->json(['message' => 'Patient ID required'], 400);
        }

        try {
            $studies = $this->fetchPatientStudies($patientId);
            
            return response()->json([
                'success' => true,
                'patient_id' => $patientId,
                'studies' => $studies,
                'count' => count($studies)
            ]);

        } catch (\Exception $e) {
            Log::error('Orthanc integration error', [
                'patient_id' => $patientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Server error while fetching studies'
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une étude spécifique
     */
    public function getStudyDetails(Request $request, string $studyId): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['radiologue', 'medecin', 'patient'])) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $response = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                ->timeout(30)
                ->get("{$this->orthancUrl}/studies/{$studyId}");

            if ($response->failed()) {
                return response()->json(['message' => 'Study not found'], 404);
            }

            $study = $response->json();
            
            // Récupération des séries de l'étude
            $seriesResponse = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                ->get("{$this->orthancUrl}/studies/{$studyId}/series");
            
            $series = $seriesResponse->successful() ? $seriesResponse->json() : [];

            return response()->json([
                'success' => true,
                'study' => [
                    'id' => $study['ID'],
                    'date' => $study['MainDicomTags']['StudyDate'] ?? null,
                    'time' => $study['MainDicomTags']['StudyTime'] ?? null,
                    'description' => $study['MainDicomTags']['StudyDescription'] ?? null,
                    'patient_name' => $study['PatientMainDicomTags']['PatientName'] ?? null,
                    'patient_id' => $study['PatientMainDicomTags']['PatientID'] ?? null,
                    'modality' => $study['MainDicomTags']['Modality'] ?? null,
                    'series_count' => count($series),
                    'series' => $this->formatSeries($series)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching study details', [
                'study_id' => $studyId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Recherche des patients par critères
     */
    public function searchPatients(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['radiologue', 'medecin'])) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $searchTerm = $request->input('search', '');
        
        try {
            $response = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                ->timeout(30)
                ->get("{$this->orthancUrl}/patients");

            if ($response->failed()) {
                return response()->json(['message' => 'Error querying Orthanc'], 500);
            }

            $patientUuids = $response->json();
            $patients = [];

            foreach ($patientUuids as $patientUuid) {
                $patientResponse = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                    ->get("{$this->orthancUrl}/patients/{$patientUuid}");
                
                if ($patientResponse->successful()) {
                    $patient = $patientResponse->json();
                    $patientName = $patient['MainDicomTags']['PatientName'] ?? '';
                    $patientId = $patient['MainDicomTags']['PatientID'] ?? '';
                    
                    // Filtrer par terme de recherche si fourni
                    if (empty($searchTerm) || 
                        stripos($patientName, $searchTerm) !== false || 
                        stripos($patientId, $searchTerm) !== false) {
                        
                        $patients[] = [
                            'uuid' => $patient['ID'],
                            'patient_id' => $patientId,
                            'name' => $patientName,
                            'birth_date' => $patient['MainDicomTags']['PatientBirthDate'] ?? null,
                            'sex' => $patient['MainDicomTags']['PatientSex'] ?? null,
                            'studies_count' => count($patient['Studies'] ?? [])
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'patients' => $patients,
                'count' => count($patients)
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching patients', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Récupère les études d'un patient depuis Orthanc
     */
    private function fetchPatientStudies(string $patientId): array
    {
        // Recherche du patient par ID
        $response = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
            ->timeout(30)
            ->get("{$this->orthancUrl}/patients");

        if ($response->failed()) {
            throw new \Exception('Failed to query Orthanc patients');
        }

        $patientUuids = $response->json();
        $studies = [];

        foreach ($patientUuids as $patientUuid) {
            // Récupération des détails du patient
            $patientResponse = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                ->get("{$this->orthancUrl}/patients/{$patientUuid}");

            if ($patientResponse->successful()) {
                $patient = $patientResponse->json();
                $currentPatientId = $patient['MainDicomTags']['PatientID'] ?? '';

                // Vérifier si c'est le patient recherché
                if ($currentPatientId === $patientId) {
                    // Récupération des études du patient
                    $studiesResponse = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                        ->get("{$this->orthancUrl}/patients/{$patientUuid}/studies");

                    if ($studiesResponse->successful()) {
                        $patientStudies = $studiesResponse->json();
                        
                        foreach ($patientStudies as $studyUuid) {
                            $studyResponse = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                                ->get("{$this->orthancUrl}/studies/{$studyUuid}");
                            
                            if ($studyResponse->successful()) {
                                $study = $studyResponse->json();
                                
                                $studies[] = [
                                    'study_id' => $study['ID'],
                                    'date' => $study['MainDicomTags']['StudyDate'] ?? null,
                                    'time' => $study['MainDicomTags']['StudyTime'] ?? null,
                                    'description' => $study['MainDicomTags']['StudyDescription'] ?? null,
                                    'modality' => $study['MainDicomTags']['Modality'] ?? null,
                                    'patient_name' => $study['PatientMainDicomTags']['PatientName'] ?? null,
                                    'patient_id' => $study['PatientMainDicomTags']['PatientID'] ?? null,
                                    'series_count' => count($study['Series'] ?? [])
                                ];
                            }
                        }
                    }
                    break; // Patient trouvé, sortir de la boucle
                }
            }
        }

        return $studies;
    }

    /**
     * Formate les séries d'une étude
     */
    private function formatSeries(array $seriesUuids): array
    {
        $series = [];
        
        foreach ($seriesUuids as $seriesUuid) {
            try {
                $seriesResponse = Http::withBasicAuth($this->orthancUsername, $this->orthancPassword)
                    ->get("{$this->orthancUrl}/series/{$seriesUuid}");
                
                if ($seriesResponse->successful()) {
                    $seriesData = $seriesResponse->json();
                    
                    $series[] = [
                        'series_id' => $seriesData['ID'],
                        'description' => $seriesData['MainDicomTags']['SeriesDescription'] ?? null,
                        'modality' => $seriesData['MainDicomTags']['Modality'] ?? null,
                        'date' => $seriesData['MainDicomTags']['SeriesDate'] ?? null,
                        'time' => $seriesData['MainDicomTags']['SeriesTime'] ?? null,
                        'instances_count' => count($seriesData['Instances'] ?? [])
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Error formatting series', ['series_uuid' => $seriesUuid]);
            }
        }
        
        return $series;
    }

    public function uploadDicom(Request $request)
{
    $request->validate([
        'file' => 'required|file',
        'id_examen_imagerie' => 'required|uuid|exists:examens_imagerie,id',
        'patient_id' => 'required|uuid|exists:patients,id',
    ]);

    try {
        // 1. Upload vers Orthanc
        $orthancUrl = 'http://localhost:8042/instances';
        $file = $request->file('file');
        
        $response = Http::attach(
            'file', 
            file_get_contents($file->getRealPath()), 
            $file->getClientOriginalName()
        )->post($orthancUrl);

        if (!$response->ok()) {
            return response()->json(['error' => 'Erreur Orthanc: ' . $response->body()], 500);
        }

        $orthancData = $response->json();

        // 2. Sauvegarde en base - Create DICOM image record
        $imageDicom = ImageDicom::create([
            'id' => Str::uuid(),
            'id_examen_imagerie' => $request->id_examen_imagerie,
            'dicom_instance_id' => $orthancData['ID'] ?? null,
            'series_id' => $orthancData['ParentSeries'] ?? null,
            'study_id' => $orthancData['ParentStudy'] ?? null,
            'patient_id' => $request->patient_id,
            'url' => 'http://localhost:8042/instances/' . ($orthancData['ID'] ?? ''),
            'preview_url' => 'http://localhost:8042/instances/' . ($orthancData['ID'] ?? '') . '/preview',
            'type' => 'dicom',
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'DICOM file uploaded successfully',
            'data' => $imageDicom,
            'orthanc_response' => $orthancData
        ], 201);

    } catch (\Exception $e) {
        \Log::error('DICOM Upload Error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erreur lors de l\'upload du fichier DICOM',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function examenImagerie(Request $request)
{
    $request->validate([
        'id_professionnel' => 'required|uuid|exists:professionnels_sante,id',
        'id_dossier_examen' => 'required|uuid|exists:dossiers_examens_complementaires,id',
        'patient_id' => 'required|uuid|exists:patients,id',
        'type' => 'required|string',
        'resultat' => 'nullable|string',
    ]);

    $id_dossier_examen = DossierExamenComplementaire::find($request->id_dossier_examen);
    $id_professionnel = ProfessionnelSante::find($request->id_professionnel);
    
    $examen = ExamenImagerie::create([
        'id' => \Str::uuid(),
        'id_professionnel' => $request->id_professionnel,
        'id_dossier_examen' => $request->id_dossier_examen,
        'patient_id' => $request->patient_id,
        'type' => $request->type,
        'resultat' => $request->resultat,
    ]);

    return response()->json($examen, 201);
}
}
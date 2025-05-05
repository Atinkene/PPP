<?php

namespace App\Http\Controllers;

use App\Models\RendezVous;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités de l'Assistant Médical
class AssistantMedicalController extends Controller
{
    // Planifier un rendez-vous
    public function planifierRendezVous(Request $request)
    {
        $request->validate([
            'id_patient' => 'required|uuid',
            'id_etablissement' => 'required|uuid',
            'date' => 'required|date',
            'type' => 'required|string',
        ]);

        $rendezVous = RendezVous::create([
            'id' => \Str::uuid(),
            'id_patient' => $request->id_patient,
            'id_etablissement' => $request->id_etablissement,
            'date' => $request->date,
            'type' => $request->type,
            'statut' => 'planifié',
        ]);

        return response()->json($rendezVous);
    }

    // Consulter les rendez-vous planifiés
    public function consulterRendezVousPlanifies()
    {
        $rendezVous = RendezVous::where('statut', 'planifié')->get();
        return response()->json($rendezVous);
    }

    // Annuler un rendez-vous
    public function annulerRendezVous($id)
    {
        $rendezVous = RendezVous::findOrFail($id);
        $rendezVous->update(['statut' => 'annulé']);
        return response()->json($rendezVous);
    }

    // Consulter les demandes de rendez-vous
    public function consulterDemandesRendezVous()
    {
        $demandes = RendezVous::where('statut', 'en_attente')->get();
        return response()->json($demandes);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Antecedent;
use App\Models\ExamenImagerie;
use Illuminate\Http\Request;

// Contrôleur pour les fonctionnalités du Biologiste
class BiologisteController extends Controller
{
    // Consulter les demandes d'examen
    public function consulterDemandesExamen()
    {
        $demandes = ExamenImagerie::whereNull('resultat')->get();
        return response()->json($demandes);
    }

    // Consulter les antécédents
    public function consulterAntecedents($idPatient)
    {
        $antecedents = Antecedent::where('id_patient', $idPatient)->get();
        return response()->json($antecedents);
    }

    // Enregistrer un résultat biologique
    public function enregistrerResultatBiologique(Request $request, $idExamen)
    {
        $request->validate(['resultat' => 'required|string']);
        $examen = ExamenImagerie::findOrFail($idExamen);
        $examen->update(['resultat' => $request->resultat]);
        return response()->json($examen);
    }

    // Signaler une anomalie urgente
    public function signalerAnomalieUrgente(Request $request, $idExamen)
    {
        $request->validate(['description' => 'required|string']);
        // Logique pour signaler l'anomalie (par exemple, notification ou table dédiée)
        return response()->json(['message' => 'Anomalie signalée']);
    }

    // Mettre à jour les données biologiques
    public function mettreAJourDonneesBiologiques(Request $request, $idExamen)
    {
        $request->validate(['resultat' => 'required|string']);
        $examen = ExamenImagerie::findOrFail($idExamen);
        $examen->update(['resultat' => $request->resultat]);
        return response()->json($examen);
    }
}
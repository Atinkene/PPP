<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: EvaluationPsychologique
class EvaluationPsychologique extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier_psycho', 'date', 'diagnostic', 'suivi', 'id_acteur'];

    public function dossierPsycho()
    {
        return $this->belongsTo(DossierPsychoSociaux::class, 'id_dossier_psycho');
    }

    public function acteur()
    {
        return $this->belongsTo(ActeurNonMedical::class, 'id_acteur');
    }
}
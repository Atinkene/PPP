<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierPsychoSociaux
class DossierPsychoSociaux extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function evaluationsPsychologiques()
    {
        return $this->hasMany(EvaluationPsychologique::class, 'id_dossier_psycho');
    }
}
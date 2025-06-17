<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: SuiviHospitalier
class SuiviHospitalier extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'id_dossier_admission', 'observation_medicale',
        'evolution_clinique', 'evenements_marquants'
    ];

    public function dossierAdmission()
    {
        return $this->belongsTo(DossierAdmissionSejour::class, 'id_dossier_admission');
    }
}
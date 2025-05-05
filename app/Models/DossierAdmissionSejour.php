<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierAdmissionSejour
class DossierAdmissionSejour extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'id_dossier_admission');
    }

    public function comptesRendusHospitalisation()
    {
        return $this->hasMany(CompteRenduHospitalisation::class, 'id_dossier_admission');
    }

    public function suivisHospitaliers()
    {
        return $this->hasMany(SuiviHospitalier::class, 'id_dossier_admission');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierPatient
class DossierPatient extends Model
{
    protected $keyType = 'string';
    protected $table = 'dossiers_patients';
    public $incrementing = false;

    protected $fillable = ['id', 'id_patient', 'id_etablissement'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }

    public function dossierAdministratif()
    {
        return $this->hasOne(DossierAdministratif::class, 'id_dossier');
    }

    public function dossierAdmissionSejour()
    {
        return $this->hasOne(DossierAdmissionSejour::class, 'id_dossier');
    }

    public function dossierSoinsMedicaux()
    {
        return $this->hasOne(DossierSoinsMedicaux::class, 'id_dossier');
    }

    public function dossierSortieSuivi()
    {
        return $this->hasOne(DossierSortieSuivi::class, 'id_dossier');
    }

    public function dossierExamensComplementaires()
    {
        return $this->hasOne(DossierExamenComplementaire::class, 'id_dossier');
    }

    public function dossierSoinsInfirmiers()
    {
        return $this->hasOne(DossierSoinsInfirmier::class, 'id_dossier');
    }

    public function dossierChirurgieAnesthesie()
    {
        return $this->hasOne(DossierChirurgieAnesthesie::class, 'id_dossier');
    }

    public function dossierPsychoSociaux()
    {
        return $this->hasOne(DossierPsychoSociaux::class, 'id_dossier');
    }
    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_dossier_patient');
    }
}

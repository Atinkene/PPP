<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: CompteRenduHospitalisation
class CompteRenduHospitalisation extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='comptes_rendus_hospitalisation';

    protected $fillable = ['id', 'id_dossier_admission', 'diagnostic_principal', 'diagnostics_associes'];

    public function dossierAdmission()
    {
        return $this->belongsTo(DossierAdmissionSejour::class, 'id_dossier_admission');
    }
}
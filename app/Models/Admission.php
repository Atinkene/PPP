<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Admission
class Admission extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier_admission', 'motif', 'id_service', 'date'];

    public function dossierAdmission()
    {
        return $this->belongsTo(DossierAdmissionSejour::class, 'id_dossier_admission');
    }

    public function service()
    {
        return $this->belongsTo(ServiceHospitalier::class, 'id_service');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: RendezVous
class RendezVous extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'rendez_vous';

    protected $fillable = [
        'id', 'id_dossier_patient', 'id_professionnel', 'id_service',
        'id_etablissement', 'date', 'type', 'statut'
    ];

    public function dossierPatient()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier_patient');
    }

    public function professionnel()
    {
        return $this->belongsTo(ProfessionnelSante::class, 'id_professionnel');
    }

    public function service()
    {
        return $this->belongsTo(ServiceHospitalier::class, 'id_service');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierSoinsInfirmier
class DossierSoinsInfirmier extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function observationsInfirmieres()
    {
        return $this->hasMany(ObservationInfirmiere::class, 'id_dossier_infirmier');
    }

    public function administrationsMedicaments()
    {
        return $this->hasMany(AdministrationMedicament::class, 'id_dossier_infirmier');
    }
}
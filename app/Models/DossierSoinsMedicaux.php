<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierSoinsMedicaux
class DossierSoinsMedicaux extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'id_dossier_soins');
    }

    public function traitements()
    {
        return $this->hasMany(Traitement::class, 'id_dossier_soins');
    }
}
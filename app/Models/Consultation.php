<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Consultation
class Consultation extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'id_dossier_soins', 'compte_rendu', 'examen_clinique',
        'symptomes', 'diagnostic', 'recommandations', 'id_professionnel'
    ];

    public function dossierSoins()
    {
        return $this->belongsTo(DossierSoinsMedicaux::class, 'id_dossier_soins');
    }

    public function professionnel()
    {
        return $this->belongsTo(ProfessionnelSante::class, 'id_professionnel');
    }

    public function symptomes()
    {
        return $this->hasMany(Symptome::class, 'id_consultation');
    }
}
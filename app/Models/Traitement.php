<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Traitement
class Traitement extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier_soins', 'type', 'date', 'description'];

    public function dossierSoins()
    {
        return $this->belongsTo(DossierSoinsMedicaux::class, 'id_dossier_soins');
    }

    public function medicaments()
    {
        return $this->hasMany(Medicament::class, 'id_traitement');
    }

    public function effetsSecondaires()
    {
        return $this->hasMany(EffetSecondaire::class, 'id_traitement');
    }
}
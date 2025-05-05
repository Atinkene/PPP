<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: CompteRenduSortie
class CompteRenduSortie extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'id_dossier_sortie', 'date', 'instructions',
        'recommandations', 'id_professionnel'
    ];

    public function dossierSortie()
    {
        return $this->belongsTo(DossierSortieSuivi::class, 'id_dossier_sortie');
    }

    public function professionnel()
    {
        return $this->belongsTo(ProfessionnelSante::class, 'id_professionnel');
    }
}

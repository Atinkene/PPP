<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierSortieSuivi
class DossierSortieSuivi extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function comptesRendusSortie()
    {
        return $this->hasMany(CompteRenduSortie::class, 'id_dossier_sortie');
    }
}

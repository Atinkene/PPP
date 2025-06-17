<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierChirurgieAnesthesie
class DossierChirurgieAnesthesie extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='dossiers_chirurgie_anesthesie';

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function fichesPreoperatoires()
    {
        return $this->hasMany(FichePreoperatoire::class, 'id_dossier_chirurgie');
    }
}
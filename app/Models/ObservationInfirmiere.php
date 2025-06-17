<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: ObservationInfirmiere
class ObservationInfirmiere extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='observations_infirmieres';

    protected $fillable = ['id', 'id_dossier_infirmier', 'date', 'etat_general', 'id_professionnel'];

    public function dossierInfirmier()
    {
        return $this->belongsTo(DossierSoinsInfirmier::class, 'id_dossier_infirmier');
    }

    public function professionnel()
    {
        return $this->belongsTo(ProfessionnelSante::class, 'id_professionnel');
    }
}

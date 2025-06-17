<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: AdministrationMedicament
class AdministrationMedicament extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='administration_medicaments';

    protected $fillable = [
        'id', 'id_medicament', 'id_dossier_infirmier',
        'id_professionnel', 'date_administration', 'dose'
    ];

    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'id_medicament');
    }

    public function dossierInfirmier()
    {
        return $this->belongsTo(DossierSoinsInfirmier::class, 'id_dossier_infirmier');
    }

    public function professionnel()
    {
        return $this->belongsTo(ProfessionnelSante::class, 'id_professionnel');
    }
}

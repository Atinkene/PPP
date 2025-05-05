<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierExamenComplementaire
class DossierExamenComplementaire extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function examensImagerie()
    {
        return $this->hasMany(ExamenImagerie::class, 'id_dossier_examen');
    }
}
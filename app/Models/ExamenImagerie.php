<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: ExamenImagerie
class ExamenImagerie extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'examens_imagerie';

    protected $fillable = [
        'id', 'id_dossier_examen', 'type', 'resultat', 'dicom_instance_id',
        'study_id', 'patient_id', 'url', 'id_professionnel'
    ];

    public function dossierExamen()
    {
        return $this->belongsTo(DossierExamenComplementaire::class, 'id_dossier_examen');
    }

    public function professionnel()
    {
        return $this->belongsTo(ProfessionnelSante::class, 'id_professionnel');
    }

    public function imagesDicom()
    {
        return $this->hasMany(ImageDicom::class, 'id_examen_imagerie');
    }
}
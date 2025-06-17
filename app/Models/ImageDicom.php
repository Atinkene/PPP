<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: ImageDicom
class ImageDicom extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='images_dicom';

    protected $fillable = [
        'id', 'id_examen_imagerie', 'orthanc_instance_id',
        'study_id', 'patient_id', 'url', 'type'
    ];

    public function examenImagerie()
    {
        return $this->belongsTo(ExamenImagerie::class, 'id_examen_imagerie');
    }
}
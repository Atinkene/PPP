<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: ActeurNonMedical
class ActeurNonMedical extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='acteurs_non_medicaux';

    protected $fillable = ['id', 'id_user', 'id_service', 'role', 'numero_adeli'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function service()
    {
        return $this->belongsTo(ServiceHospitalier::class, 'id_service');
    }

    public function evaluationsPsychologiques()
    {
        return $this->hasMany(EvaluationPsychologique::class, 'id_acteur');
    }
}

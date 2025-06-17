<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Medicament
class Medicament extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_traitement', 'nom', 'posologie', 'duree'];

    public function traitement()
    {
        return $this->belongsTo(Traitement::class, 'id_traitement');
    }

    public function administrations()
    {
        return $this->hasMany(AdministrationMedicament::class, 'id_medicament');
    }

    public function effetsSecondaires()
    {
        return $this->hasMany(EffetSecondaire::class, 'id_medicament');
    }
}

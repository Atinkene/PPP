<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Antecedent
class Antecedent extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'dossiers_soins_medicaux'];

    public function dossierSoinsMedicaux()
    {
        return $this->belongsTo(DossierSoinsMedicaux::class, 'dossiers_soins_medicaux');
    }

    public function allergies()
    {
        return $this->hasMany(Allergie::class, 'id_antecedent');
    }

    public function maladiesChroniques()
    {
        return $this->hasMany(MaladieChronique::class, 'id_antecedent');
    }

    public function traitementsLongueDuree()
    {
        return $this->hasMany(TraitementLongueDuree::class, 'id_antecedent');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'id_antecedent');
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class, 'id_antecedent');
    }
}

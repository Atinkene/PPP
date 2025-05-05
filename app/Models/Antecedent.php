<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Antecedent
class Antecedent extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_patient', 'lien_parente', 'maladie', 'age_apparition', 'deces'];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_patient');
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

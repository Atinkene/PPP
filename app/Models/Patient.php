<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Modèle: Patient
class Patient extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_user', 'groupe_sanguin'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function dossiersPatients()
    {
        return $this->hasMany(DossierPatient::class, 'id_patient');
    }

    public function antecedents()
    {
        return $this->hasMany(Antecedent::class, 'id_patient');
    }

    public function contactsUrgence()
    {
        return $this->hasMany(ContactUrgence::class, 'id_patient');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_patient');
    }
}
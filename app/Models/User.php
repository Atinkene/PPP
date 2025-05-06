<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Modèle: User
class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'email', 'nom', 'prenom', 'sexe', 'date_naissance', 'cin',
        'lieu_naissance', 'nationalite', 'adresse_postale', 'numero_telephone',
        'login', 'password','role',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function patient()
    {
        return $this->hasOne(Patient::class, 'id_user');
    }

    public function professionnelSante()
    {
        return $this->hasOne(ProfessionnelSante::class, 'id_user');
    }

    public function acteurNonMedical()
    {
        return $this->hasOne(ActeurNonMedical::class, 'id_user');
    }

    public function etablissements()
    {
        return $this->hasMany(Etablissement::class, 'directeur');
    }

    public function servicesHospitaliers()
    {
        return $this->hasMany(ServiceHospitalier::class, 'chef_service');
    }

    public function contactsUrgence()
    {
        return $this->hasMany(ContactUrgence::class, 'id_user_contact');
    }
    
}
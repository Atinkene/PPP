<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierAdministratif
class DossierAdministratif extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='dossiers_administratifs';
    protected $fillable = ['id', 'id_dossier', 'id_contact'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'id_contact');
    }

    public function assurances()
    {
        return $this->hasMany(Assurance::class, 'id_dossier_admin');
    }

    public function consentements()
    {
        return $this->hasMany(Consentement::class, 'id_dossier_admin');
    }
    public function antecedents()
    {
        return $this->hasMany(Antecedents::class, 'id_dossier_admin');
    }
    public function contactsUrgences()
    {
        return $this->hasMany(ContactUrgence::class, 'id_dossier_admin');
    }
    
}
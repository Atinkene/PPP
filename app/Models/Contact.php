<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Contact
class Contact extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'adresse_postale', 'numero_telephone', 'email','id_dossier_admin'];

    public function dossierAdministratif()
    {
        return $this->hasOne(DossierAdministratif::class, 'id_dossier_admin');
    }
}
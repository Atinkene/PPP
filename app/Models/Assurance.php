<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Assurance
class Assurance extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'numero_securite_social', 'organisme_assurance_sante', 'prise_en_charge'];

    public function dossierAdministratif()
    {
        return $this->hasOne(DossierAdministratif::class, 'id_assurance');
    }
}
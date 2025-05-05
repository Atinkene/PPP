<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Consentement
class Consentement extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'type', 'statut', 'date_autorisation'];

    public function dossierAdministratif()
    {
        return $this->hasOne(DossierAdministratif::class, 'id_consentement');
    }
}

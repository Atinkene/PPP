<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Consentement
class Consentement extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'type', 'statut', 'date_autorisation', 'id_dossier_admin'];

    public function dossierAdministratif()
    {
        return $this->belongsTo(DossierAdministratif::class, 'id_dossier_admin');
    }
}

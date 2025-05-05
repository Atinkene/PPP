<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: DossierAdministratif
class DossierAdministratif extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier', 'id_contact', 'id_assurance', 'id_consentement'];

    public function dossier()
    {
        return $this->belongsTo(DossierPatient::class, 'id_dossier');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'id_contact');
    }

    public function assurance()
    {
        return $this->belongsTo(Assurance::class, 'id_assurance');
    }

    public function consentement()
    {
        return $this->belongsTo(Consentement::class, 'id_consentement');
    }
}
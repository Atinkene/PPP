<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: ContactUrgence
class ContactUrgence extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='contacts_urgence';

    protected $fillable = [
        'id', 'id_patient', 'lien_parente', 'cause',
        'date', 'est_joint', 'id_user_contact'
    ];

    public function dossierAdministratif()
    {
        return $this->belongsTo(DossierAdministratif::class, 'id_dossier_admin');
    }

    public function userContact()
    {
        return $this->belongsTo(User::class, 'id_user_contact');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: ProfessionnelSante
class ProfessionnelSante extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'professionnels_sante';

    protected $fillable = ['id', 'id_user', 'id_service', 'numero_rpps', 'type', 'specialite'];

    public function user() 
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function service()
    {
        return $this->belongsTo(ServiceHospitalier::class, 'id_service');
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'id_professionnel');
    }

    public function comptesRendusSortie()
    {
        return $this->hasMany(CompteRenduSortie::class, 'id_professionnel');
    }

    public function examensImagerie()
    {
        return $this->hasMany(ExamenImagerie::class, 'id_professionnel');
    }

    public function observationsInfirmieres()
    {
        return $this->hasMany(ObservationInfirmiere::class, 'id_professionnel');
    }

    public function administrationsMedicaments()
    {
        return $this->hasMany(AdministrationMedicament::class, 'id_professionnel');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_professionnel');
    }
}
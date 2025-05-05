<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Modèle: ServiceHospitalier
class ServiceHospitalier extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_etablissement', 'nom', 'chef_service'];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'id_etablissement');
    }

    public function chefService()
    {
        return $this->belongsTo(User::class, 'chef_service');
    }

    public function professionnelsSante()
    {
        return $this->hasMany(ProfessionnelSante::class, 'id_service');
    }

    public function acteursNonMedicaux()
    {
        return $this->hasMany(ActeurNonMedical::class, 'id_service');
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class, 'id_service');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_service');
    }
}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Etablissement
class Etablissement extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'nom', 'adresse', 'type', 'contact', 'directeur'];

    public function directeur()
    {
        return $this->belongsTo(User::class, 'directeur');
    }

    public function servicesHospitaliers()
    {
        return $this->hasMany(ServiceHospitalier::class, 'id_etablissement');
    }

    public function dossiersPatients()
    {
        return $this->hasMany(DossierPatient::class, 'id_etablissement');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'id_etablissement');
    }
}
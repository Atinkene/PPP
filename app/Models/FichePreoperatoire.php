<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: FichePreoperatoire
class FichePreoperatoire extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_dossier_chirurgie', 'date', 'nom'];

    public function dossierChirurgie()
    {
        return $this->belongsTo(DossierChirurgieAnesthesie::class, 'id_dossier_chirurgie');
    }
}
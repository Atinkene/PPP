<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: EffetSecondaire
class EffetSecondaire extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='effets_secondaires';

    protected $fillable = ['id', 'id_traitement', 'id_medicament', 'nom', 'date'];

    public function traitement()
    {
        return $this->belongsTo(Traitement::class, 'id_traitement');
    }

    public function medicament()
    {
        return $this->belongsTo(Medicament::class, 'id_medicament');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Symptome
class Symptome extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_consultation', 'nom', 'date'];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'id_consultation');
    }
}

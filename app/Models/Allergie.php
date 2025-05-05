<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: Allergie
class Allergie extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'id_antecedent', 'nom'];

    public function antecedent()
    {
        return $this->belongsTo(Antecedent::class, 'id_antecedent');
    }
}
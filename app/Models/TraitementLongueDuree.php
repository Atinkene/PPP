<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: TraitementLongueDuree
class TraitementLongueDuree extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='traitements_longue_duree';

    protected $fillable = ['id', 'id_antecedent', 'nom'];

    public function antecedent()
    {
        return $this->belongsTo(Antecedent::class, 'id_antecedent');
    }
}
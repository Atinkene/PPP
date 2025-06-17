<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Modèle: MaladieChronique
class MaladieChronique extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table ='maladies_chroniques';

    protected $fillable = ['id', 'id_antecedent', 'nom'];

    public function antecedent()
    {
        return $this->belongsTo(Antecedent::class, 'id_antecedent');
    }
}
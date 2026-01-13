<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    protected $table = 'cidade';


    protected $fillable = [
        'nome',
    ];

// Relação com endereços
    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'cidade_id');
    }
    
}

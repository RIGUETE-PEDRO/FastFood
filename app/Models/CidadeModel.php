<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CidadeModel extends Model
{
    protected $table = 'cidade';


    protected $fillable = [
        'nome',
    ];

// Relação com endereços
    public function enderecos()
    {
        return $this->hasMany(EnderecoModel::class, 'cidade_id');
    }

}

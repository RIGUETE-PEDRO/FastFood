<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Endereco extends Model
{
    protected $table = 'endereco';

    protected $fillable = [
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade_id',
    ];

    // Relação com a cidade
    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}

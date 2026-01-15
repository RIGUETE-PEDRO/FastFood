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
        'usuario_id',
    ];

    // Relação com a cidade
    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'endereco_id');
    }
}

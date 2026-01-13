<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $fillable = [
        'usuario_id',
        'status',
        'tipo_pagamento_id',
        'valor_total',
        'observacoes_pagamento',
        'endereco_id',
    ];
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $fillable = [
        'usuario_id',
        'status_id',
        'forma_pagamento_id',
        'total',
        'observacoes_pagamento',
    ];
}

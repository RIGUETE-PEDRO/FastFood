<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FormaPagamentoModel extends Model
{
    protected $table = 'tipo_pagamento';
    protected $fillable = [
        'tipo_pagamento',
    ];

    public function pedidos()
    {
        return $this->hasMany(PedidoModel::class, 'tipo_pagamento_id');
    }
}

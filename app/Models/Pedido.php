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
    public function itens()
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    public function statusRelacionamento()
    {
        return $this->belongsTo(Status::class, 'status');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'tipo_pagamento_id');
    }
}

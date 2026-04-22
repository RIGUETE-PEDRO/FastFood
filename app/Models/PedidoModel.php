<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PedidoModel extends Model
{
    protected $table = 'pedidos';
    protected $fillable = [
        'usuario_id',
        'motoboy_id',
        'motoboy_vinculado_em',
        'mesa_id',
        'status',
        'tipo_pagamento_id',
        'valor_total',
        'observacoes_pagamento',
        'endereco_id',
    ];


    protected $guarded = [
        'deleted'
    ];

    public function itens()
    {
        return $this->hasMany(ItemPedidoModel::class, 'pedido_id');
    }

    public function statusRelacionamento()
    {
        return $this->belongsTo(StatusModel::class, 'status');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function motoboy()
    {
        return $this->belongsTo(UsuarioModel::class, 'motoboy_id');
    }

    public function endereco()
    {
        return $this->belongsTo(EnderecoModel::class, 'endereco_id');
    }

    public function mesa()
    {
        return $this->belongsTo(MesaModel::class, 'mesa_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamentoModel::class, 'tipo_pagamento_id');
    }
}

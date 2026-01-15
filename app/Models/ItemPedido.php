<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    protected $table = 'item_pedido';
    protected $fillable = [
        'preco_unitario',
        'quantidade',
        'produto_id',
        'usuario_id',
        'pedido_id',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}

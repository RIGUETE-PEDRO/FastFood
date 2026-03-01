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

    protected $guarded = [
        'deleted'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

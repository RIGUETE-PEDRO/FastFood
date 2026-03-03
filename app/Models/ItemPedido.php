<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    protected $table = 'item_pedido';
    protected $fillable = [
        'preco_unitario',
        'valor_pago',
        'quantidade',
        'status_da_comanda',
        'pago_em',
        'pagamento_metodo',
        'produto_id',
        'usuario_id',
        'pedido_id',
        'mesa_id',
    ];

    protected $guarded = [
        'deleted'
    ];

    protected $casts = [
        'pago_em' => 'datetime',
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

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }
}

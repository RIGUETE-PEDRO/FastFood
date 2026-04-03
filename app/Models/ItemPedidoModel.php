<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ItemPedidoModel extends Model
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
        return $this->belongsTo(ProdutoModel::class, 'produto_id');
    }

    public function pedido()
    {
        return $this->belongsTo(PedidoModel::class, 'pedido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function mesa()
    {
        return $this->belongsTo(MesaModel::class, 'mesa_id');
    }
}

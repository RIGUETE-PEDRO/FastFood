<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MesaModel extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'numero_da_mesa',
        'status',
        'preco',
    ];

    protected $guarded = [
        'deleted',
    ];

    public function pedidos()
    {
        return $this->hasMany(PedidoModel::class, 'mesa_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
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
        return $this->hasMany(Pedido::class, 'mesa_id');
    }
}

<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StatusModel extends Model
{
    protected $table = 'status';
    protected $fillable = [
        'status',
    ];


     public function pedidos()
    {
        return $this->hasMany(PedidoModel::class, 'status');
    }
}

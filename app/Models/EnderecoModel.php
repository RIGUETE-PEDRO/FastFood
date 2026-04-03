<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EnderecoModel extends Model
{
    protected $table = 'endereco';

    protected $fillable = [
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade_id',
        'usuario_id',
    ];

    protected $guarded = [
        'deleted'
    ];

    // Relação com a cidade
    public function cidade()
    {
        return $this->belongsTo(CidadeModel::class, 'cidade_id');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(PedidoModel::class, 'endereco_id');
    }
}

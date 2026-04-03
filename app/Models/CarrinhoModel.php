<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class CarrinhoModel extends Model
{
    protected $table = 'carrinho';

    protected $fillable = [
        'usuario_id',
        'produto_id',
        'quantidade',
        'observacao',
        'preco_total',

    ];

    protected $guarded = [
        'deleted'
    ];


    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function produto()
    {
        return $this->belongsTo(ProdutoModel::class, 'produto_id');
    }
}

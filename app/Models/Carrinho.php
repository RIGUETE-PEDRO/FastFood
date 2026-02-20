<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Carrinho extends Model
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
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}

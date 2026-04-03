<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoModel extends Model
{
    protected $table = 'produtos';
    protected $fillable = [
        'nome',
        'preco',
        'descricao',
        'imagem_url',
        'disponivel',
        'categoria_id',
    ];

    protected $guarded = [
        'deleted'
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProdutoModel::class, 'categoria_id');
    }
}

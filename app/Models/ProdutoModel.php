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
        'no_carrousel',
        'precos_por_tamanho',
        'aceita_meia_por',
        'preco_meia_por',
    ];

    protected $casts = [
        'precos_por_tamanho' => 'array',
        'aceita_meia_por' => 'boolean',
    ];

    protected $guarded = [
        'deleted'
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaProdutoModel::class, 'categoria_id');
    }

    public function bordas()
    {
        return $this->belongsToMany(BordaModel::class, 'produto_borda', 'produto_id', 'borda_id');
    }
}

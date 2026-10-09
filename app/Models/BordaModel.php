<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BordaModel extends Model
{
    protected $table = 'bordas';

    protected $fillable = ['nome', 'adicionais_por_tamanho', 'ativa'];

    protected $casts = [
        'adicionais_por_tamanho' => 'array',
        'ativa' => 'boolean',
        'padrao' => 'boolean',
    ];

    public function produtos()
    {
        return $this->belongsToMany(ProdutoModel::class, 'produto_borda', 'borda_id', 'produto_id');
    }
}

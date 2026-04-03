<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaProdutoModel extends Model
{
    // Corrige para o nome real da tabela
    protected $table = 'categoria_produto';
    protected $fillable = [
        'nome',
    ];

    protected $guarded = [
        'deleted'
    ];

    public function produtos()
    {
        return $this->hasMany(ProdutoModel::class, 'categoria_id');
    }
}

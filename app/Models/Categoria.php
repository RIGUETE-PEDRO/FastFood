<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
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
        return $this->hasMany(Produto::class, 'categoria_id');
    }
}

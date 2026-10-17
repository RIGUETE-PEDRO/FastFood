<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

    public function getBordasDisponiveisAttribute()
    {
        $categoria = $this->relationLoaded('categoria')
            ? $this->getRelation('categoria')
            : $this->categoria()->first();
        $nomeCategoria = mb_strtolower(Str::ascii(trim((string) optional($categoria)->nome)));

        if (!in_array($nomeCategoria, ['pizza', 'pizzas'], true)) {
            return collect();
        }

        return Cache::remember('bordas_ativas', now()->addMinutes(30), function () {
            return BordaModel::query()->where('ativa', true)->orderBy('nome')->get();
        });
    }
}

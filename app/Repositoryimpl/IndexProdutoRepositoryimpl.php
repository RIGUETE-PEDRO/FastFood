<?php

namespace App\Repositoryimpl;

use App\Models\ProdutoModel;
use App\Repository\IndexProdutoRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IndexProdutoRepositoryimpl implements IndexProdutoRepository
{
    public function pegarProdutosIndex()
    {
        Log::info('IndexProdutoRepositoryimpl: carregando produtos para a home');
        
        return Cache::remember("todos_produtos",now()->addMinutes(30),function (){
              return ProdutoModel::where('disponivel', true)
                ->orderBy('nome')
                ->get();
        });
    }

    public function pegarProdutosDestaque()
    {
        Log::info('IndexProdutoRepositoryimpl: carregando produtos em destaque');

        return Cache::remember('lista_produtos_destaque', now()->addMinutes(30), function () {
            return ProdutoModel::where('disponivel', true)
                ->where('no_carrousel', true)
                ->orderBy('nome')
                ->get();
        });
    }
        
}

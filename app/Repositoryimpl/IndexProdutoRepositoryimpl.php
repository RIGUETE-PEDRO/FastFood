<?php

namespace App\Repositoryimpl;

use App\Models\ProdutoModel;
use App\Repository\IndexProdutoRepository;
use Illuminate\Support\Facades\Log;

class IndexProdutoRepositoryimpl implements IndexProdutoRepository
{
    public function pegarProdutosIndex()
    {
        Log::info('IndexProdutoRepositoryimpl: carregando produtos para a home');

        return ProdutoModel::where('disponivel', true)
            ->where('no_carrousel', true)
            ->get();
    }
}

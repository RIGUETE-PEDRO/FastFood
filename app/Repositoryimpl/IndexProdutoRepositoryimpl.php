<?php

namespace App\Repositoryimpl;

use App\Models\ProdutoModel;
use App\Repository\IndexProdutoRepository;
use Illuminate\Support\Facades\Log;

class IndexProdutoRepositoryimpl implements IndexProdutoRepository
{
    public function pegarProdutosIndex()
    {
        return ProdutoModel::where('disponivel', true)
            ->get();
    }
}

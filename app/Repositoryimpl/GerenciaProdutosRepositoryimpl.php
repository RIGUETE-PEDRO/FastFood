<?php

namespace App\Repositoryimpl;

use App\Models\CategoriaProdutoModel;
use App\Models\ProdutoModel;

class GerenciaProdutosRepositoryimpl
{
    public function listarProdutosComCategoria()
    {
        return ProdutoModel::with('categoria')->get();
    }

    public function listarCategorias()
    {
        return CategoriaProdutoModel::all();
    }

    public function criarProduto(array $dados): ProdutoModel
    {
        return ProdutoModel::create($dados);
    }

    public function buscarProdutoPorId(int $id): ?ProdutoModel
    {
        return ProdutoModel::find($id);
    }
}

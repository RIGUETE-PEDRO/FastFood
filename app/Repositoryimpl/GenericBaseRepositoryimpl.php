<?php

namespace App\Repositoryimpl;

use App\Models\CarrinhoModel;
use App\Models\CidadeModel;
use App\Models\FuncionarioModel;
use App\Models\ProdutoModel;
use App\Models\UsuarioModel;
use App\Repository\GenericBaseRepository;
use Illuminate\Support\Facades\Cache;

class GenericBaseRepositoryimpl implements  GenericBaseRepository
{
    public function pegarUsuarioEmail(array $data): ?UsuarioModel
    {
        return UsuarioModel::where('email', $data['email'])->first();
    }

    public function existeFuncionario(UsuarioModel $usuario): ?FuncionarioModel
    {
        return FuncionarioModel::where('usuario_id', $usuario->id)->first();
    }

    public function findById(int $id): ?UsuarioModel
    {
        return UsuarioModel::find($id);
    }

    public function findByProdutos(array $categorias)
    {
        Cache::remember('produtos_categorias', now()->addMinutes(60), function () use ($categorias) {
            return ProdutoModel::query()
                ->where('disponivel', true)
                ->whereHas('categoria', function ($query) use ($categorias) {
                    $query->whereIn('nome', $categorias)
                        ->where('deleted', false);
                })
                ->get();
        });
    }

    public function pegarProdutos()
    {
        return ProdutoModel::where('disponivel', true)->get();
    }

    public function findAll()
    {
        return UsuarioModel::all();
    }

    public function findByCidade()
    {
        return Cache::rememberForever('lista_cidades', function () {
            return CidadeModel::orderBy('nome')->get();
        });
    }

    public function findFuncionarios()
    {
        return Cache::remember('List_funcionario', now()->addMinutes(60), function () {
            return FuncionarioModel::with('usuario')->get();
        });
    }

    public function deleteFuncionarioEUsuario(int $usuarioId): bool
    {
        $funcionario = FuncionarioModel::where('usuario_id', $usuarioId)->first();

        if ($funcionario) {
            $funcionario->delete();
        }

        $usuario = UsuarioModel::find($usuarioId);
        if ($usuario) {
            $deletou = (bool) $usuario->delete();

            if ($deletou) {
                Cache::forget('List_funcionario');
            }

            return $deletou;
        }

        return false;
    }

    public function pegarItensCarrinho(int $usuarioId)
    {
        return CarrinhoModel::with('produto')
            ->where('usuario_id', $usuarioId)
            ->get();
    }

    public function findByProdutosIsUsuario(int $produtoId, int $usuarioId)
    {
        return CarrinhoModel::where('produto_id', $produtoId)
            ->where('usuario_id', $usuarioId)
            ->first();
    }
}

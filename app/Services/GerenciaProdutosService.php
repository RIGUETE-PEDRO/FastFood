<?php

namespace App\Services;

use App\Repository\GerenciaProdutosRepository;

class GerenciaProdutosService
{
    protected GenericBase $genericBase;
    protected GerenciaProdutosRepository $repository;

    public function __construct(GenericBase $genericBase, GerenciaProdutosRepository $repository)
    {
        $this->genericBase = $genericBase;
        $this->repository = $repository;
    }

    public function gerenciarProdutos()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $nomeUsuario = $usuarioLogado ? explode(' ', trim($usuarioLogado->nome))[0] : 'Usuário';
        $produtos = $this->repository->listarProdutosComCategoria();
        $categorias = $this->repository->listarCategorias();

        return compact('usuarioLogado', 'nomeUsuario', 'produtos', 'categorias');
    }

    public function criarProduto($request)
    {
        $preco = str_replace(',', '.', $request->preco);
        $produto = $this->repository->criarProduto(
            [
                'nome' => $request->input('nome'),
                'preco' => $preco,
                'descricao' => $request->input('descricao'),
                'imagem_url' => $request->input('imagem'),
                'disponivel' => $request->input('ativo'),
                'categoria_id' => $request->input('categoria_id'),
            ]
        );

        if ($request->hasFile('imagem')) {
            $file = $request->file('imagem');
            $filename = uniqid('produto_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/produtos'), $filename);
            $produto->imagem_url = $filename;
        }else{
            $produto->imagem_url = 'sem_imagem.jpg';
        }

        return $this->repository->salvarProduto($produto);
    }

    public function removerProduto($id): bool
    {
        $usuarioLogado =  $this->genericBase->hasLogado();

        if (($usuarioLogado->tipo ?? null) === 'Administrador') {
            return $this->repository->deletarProduto((int) $id);
        }

        return false;
    }

    public function atualizarCarrousel($id, bool $noCarrousel)
    {
        return $this->repository->atualizarCarrousel((int) $id, $noCarrousel);
    }

    public function atualizarProduto($id, $data)
    {
        return $this->repository->atualizarProduto((int) $id, $data);
    }

}

<?php

namespace App\Services;

use App\Repository\GerenciaProdutosRepository;

class GerenciaProdutosService
{
    protected GenericBase $genericBase;
    protected GerenciaProdutosRepository $repository;

    public function __construct(
        GenericBase $genericBase,
        GerenciaProdutosRepository $repository,
        private OtimizadorImagemProduto $otimizadorImagemProduto,
    )
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
                'imagem_url' => $request->hasFile('imagem')
                    ? $this->otimizadorImagemProduto->salvar($request->file('imagem'))
                    : 'sem_imagem.jpg',
                'disponivel' => $request->input('ativo'),
                'categoria_id' => $request->input('categoria_id'),
            ]
        );

        return $produto;
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
        if (($data['imagem'] ?? null) instanceof \Illuminate\Http\UploadedFile) {
            $data['imagem_url'] = $this->otimizadorImagemProduto->salvar($data['imagem']);
        }

        return $this->repository->atualizarProduto((int) $id, $data);
    }

}

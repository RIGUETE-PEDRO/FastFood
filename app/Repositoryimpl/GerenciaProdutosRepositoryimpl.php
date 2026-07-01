<?php

namespace App\Repositoryimpl;

use App\Models\CategoriaProdutoModel;
use App\Models\ProdutoModel;
use App\Repository\GerenciaProdutosRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class GerenciaProdutosRepositoryimpl implements GerenciaProdutosRepository
{
    public function listarProdutosComCategoria()
    {
        return Cache::remember('lista_produtos', now()->addMinutes(30), function () {
            return ProdutoModel::with('categoria')->get();
        });
    }

    public function listarCategorias()
    {
        return Cache::rememberForever('lista_categorias', function () {
            return CategoriaProdutoModel::all();
        });
    }

    public function criarProduto(array $dados): ProdutoModel
    {
        $produto = ProdutoModel::create($dados);
        Cache::forget('lista_produtos');
        Cache::forget('lista_produtos_destaque');
        Cache::forget('todos_produtos');

        return $produto;
    }

    public function salvarProduto(ProdutoModel $produto): ProdutoModel
    {
        $produto->save();
        Cache::forget('lista_produtos');
        Cache::forget('lista_produtos_destaque');
        Cache::forget('todos_produtos');
         

        return $produto;
    }

    public function buscarProdutoPorId(int $id): ?ProdutoModel
    {
        return ProdutoModel::find($id);
    }

    public function atualizarProduto(int $id, array $data): ?ProdutoModel
    {
        $produto = $this->buscarProdutoPorId($id);

        if (!$produto) {
            return null;
        }

        $produto->nome = $data['nome'];
        $produto->preco = str_replace(',', '.', $data['preco']);
        $produto->descricao = $data['descricao'];
        $produto->disponivel = $data['ativo'];
        $produto->categoria_id = $data['categoria_id'];

        if (($data['imagem'] ?? null) instanceof UploadedFile && $data['imagem']->isValid()) {
            $this->removerImagemProduto($produto);

            $file = $data['imagem'];
            $filename = uniqid('produto_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/produtos'), $filename);
            $produto->imagem_url = $filename;
        }
        Cache::forget('lista_produtos');
        Cache::forget('lista_produtos_destaque');
        Cache::forget('todos_produtos');

        return $this->salvarProduto($produto);
    }

    public function deletarProduto(int $id): bool
    {
        $produto = $this->buscarProdutoPorId($id);

        if (!$produto) {
            return false;
        }

        $this->removerImagemProduto($produto);

        $deletou = (bool) $produto->delete();

        if ($deletou) {
            Cache::forget('lista_produtos');
            Cache::forget('lista_produtos_destaque');
            Cache::forget('todos_produtos');
        }

        return $deletou;
    }

    public function atualizarCarrousel(int $id, bool $noCarrousel): ProdutoModel
    {
        $produto = ProdutoModel::findOrFail($id);

        $produto->update([
            'no_carrousel' => $noCarrousel,
        ]);

        Cache::forget('lista_produtos');
        Cache::forget('lista_produtos_destaque');
        

        return $produto;
    }

    private function removerImagemProduto(ProdutoModel $produto): void
    {
        if (
            $produto->imagem_url
            && $produto->imagem_url !== 'sem_imagem.jpg'
            && file_exists(public_path('img/produtos/' . $produto->imagem_url))
        ) {
            unlink(public_path('img/produtos/' . $produto->imagem_url));
        }
    }
}

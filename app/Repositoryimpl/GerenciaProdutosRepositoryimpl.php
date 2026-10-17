<?php

namespace App\Repositoryimpl;

use App\Models\CategoriaProdutoModel;
use App\Models\ProdutoModel;
use App\Repository\GerenciaProdutosRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        Cache::forget('produtos_categorias');

        return $produto;
    }

    public function salvarProduto(ProdutoModel $produto): ProdutoModel
    {
        $produto->save();
        Cache::forget('lista_produtos');
        Cache::forget('lista_produtos_destaque');
        Cache::forget('todos_produtos');
        Cache::forget('produtos_categorias');


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
        $produto->precos_por_tamanho = $data['precos_por_tamanho'] ?? null;
        $produto->aceita_meia_por = $data['aceita_meia_por'] ?? false;
        $produto->preco_meia_por = $data['preco_meia_por'] ?? null;

        if (!empty($data['imagem_url'])) {
            $this->removerImagemProduto($produto);
            $produto->imagem_url = $data['imagem_url'];
        }
        Cache::forget('lista_produtos');
        Cache::forget('lista_produtos_destaque');
        Cache::forget('todos_produtos');
        Cache::forget('produtos_categorias');

        return $this->salvarProduto($produto);
    }

   public function deletarProduto(int $id): bool
    {
        $produto = $this->buscarProdutoPorId($id);

        if (!$produto) {
            return false;
        }

        $deletou = DB::transaction(function () use ($produto) {
            DB::table('item_pedido')
                ->where('produto_id', $produto->id)
                ->delete();

            return (bool) $produto->delete();
        });

        if ($deletou) {
            $this->removerImagemProduto($produto);

            Cache::forget('lista_produtos');
            Cache::forget('lista_produtos_destaque');
            Cache::forget('todos_produtos');
            Cache::forget('produtos_categorias');
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
        Cache::forget('produtos_categorias');

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

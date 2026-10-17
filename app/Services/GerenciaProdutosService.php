<?php

namespace App\Services;

use App\Models\CategoriaProdutoModel;
use App\Repository\GerenciaProdutosRepository;
use Illuminate\Support\Str;

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

    public function criarProduto($request, ?array $dadosValidados = null)
    {
        $dados = $dadosValidados ?? $request->all();
        $opcoes = $this->opcoesPersonalizacao($dados);
        $produto = $this->repository->criarProduto(
            [
                'nome' => $dados['nome'],
                'preco' => $this->precoDecimal($dados['preco']),
                'descricao' => $dados['descricao'] ?? null,
                'imagem_url' => $request->hasFile('imagem')
                    ? $this->otimizadorImagemProduto->salvar($request->file('imagem'))
                    : 'sem_imagem.jpg',
                'disponivel' => (bool) $dados['ativo'],
                'categoria_id' => $dados['categoria_id'],
                ...$opcoes,
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

        $data['preco'] = $this->precoDecimal($data['preco']);
        $data = array_merge($data, $this->opcoesPersonalizacao($data));

        $produto = $this->repository->atualizarProduto((int) $id, $data);
        return $produto;
    }

    private function opcoesPersonalizacao(array $dados): array
    {
        $categoria = CategoriaProdutoModel::find($dados['categoria_id'] ?? null);
        $nomeCategoria = mb_strtolower(Str::ascii(trim((string) optional($categoria)->nome)));

        if (in_array($nomeCategoria, ['pizza', 'pizzas'], true)) {
            $precos = [];
            foreach (['P', 'M', 'G', 'GG', 'XG'] as $tamanho) {
                $valor = $dados['precos_por_tamanho'][$tamanho] ?? null;
                if ($valor !== null && $valor !== '') {
                    $precos[$tamanho] = $this->precoDecimal($valor);
                }
            }

            return [
                'precos_por_tamanho' => $precos ?: null,
                'aceita_meia_por' => false,
                'preco_meia_por' => null,
            ];
        }

        if (in_array($nomeCategoria, ['porcao', 'porcoes'], true)) {
            $aceitaMeia = filter_var($dados['aceita_meia_por'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'precos_por_tamanho' => null,
                'aceita_meia_por' => $aceitaMeia,
                'preco_meia_por' => $aceitaMeia && filled($dados['preco_meia_por'] ?? null)
                    ? $this->precoDecimal($dados['preco_meia_por'])
                    : null,
            ];
        }

        return [
            'precos_por_tamanho' => null,
            'aceita_meia_por' => false,
            'preco_meia_por' => null,
        ];
    }

    private function precoDecimal(mixed $valor): string
    {
        return number_format((float) str_replace(',', '.', (string) $valor), 2, '.', '');
    }

}

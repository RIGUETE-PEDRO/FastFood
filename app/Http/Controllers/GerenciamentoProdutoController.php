<?php
namespace App\Http\Controllers;

use App\Mensagens\PassMensagens;
use App\Services\GerenciaProdutosService;
use App\Services\GenericBase;
use App\Services\AuthService;
use App\Models\BordaModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;


class GerenciamentoProdutoController extends Controller
{
    protected GenericBase $genericBase;
    protected AuthService $authService;
    protected GerenciaProdutosService $gerenciaProdutosService;

    public function __construct(GenericBase $genericBase, AuthService $authService, GerenciaProdutosService $gerenciaProdutosService)
    {
        $this->genericBase = $genericBase;
        $this->authService = $authService;
        $this->gerenciaProdutosService = $gerenciaProdutosService;
    }

    public function gerenciamentoProduto()
    {
        $dados = $this->gerenciaProdutosService->gerenciarProdutos();

        return view('Admin.GerenciamentoProduto', [
            'produtos' => $dados['produtos'] ?? [],
            'categorias' => $dados['categorias'] ?? [],
            'usuario' => $dados['usuarioLogado'] ?? null,
            'nomeUsuario' => $dados['nomeUsuario'] ?? 'Usuário',
        ]);
    }


    public function cadastrarProduto(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:120',
            'preco' => ['required', 'regex:/^(?=.*[1-9])\d{1,8}([.,]\d{1,2})?$/'],
            'descricao' => 'nullable|string|max:2000',
            'ativo' => 'required|boolean',
            'categoria_id' => 'required|integer|exists:categoria_produto,id',
            'imagem' => 'nullable|image|mimes:jpeg,png,webp|max:10240',
            'precos_por_tamanho' => 'nullable|array:P,M,G,GG,XG',
            'precos_por_tamanho.*' => 'nullable|numeric|min:0.01',
            'aceita_meia_por' => 'nullable|boolean',
            'preco_meia_por' => 'nullable|required_if:aceita_meia_por,1|numeric|min:0.01',
        ]);
        $this->gerenciaProdutosService->criarProduto($request, $dados);
        return redirect()->route('ListaProdutos')->with('success', PassMensagens::CADASTRAR_PRODUTO_SUCESSO);
    }

    public function deletarProduto($id)
    {
        $this->gerenciaProdutosService->removerProduto($id);
        return redirect()->route('gerenciamento_Produtos')->with('success', PassMensagens::DELETE_SUCESSO);
    }

    public function atualizarProduto(Request $request, $id)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:120',
            'preco' => ['required', 'regex:/^(?=.*[1-9])\d{1,8}([.,]\d{1,2})?$/'],
            'descricao' => 'nullable|string|max:2000',
            'ativo' => 'required|boolean',
            'categoria_id' => 'required|integer|exists:categoria_produto,id',
            'imagem' => 'nullable|image|mimes:jpeg,png,webp|max:10240',
            'precos_por_tamanho' => 'nullable|array:P,M,G,GG,XG',
            'precos_por_tamanho.*' => 'nullable|numeric|min:0.01',
            'aceita_meia_por' => 'nullable|boolean',
            'preco_meia_por' => 'nullable|required_if:aceita_meia_por,1|numeric|min:0.01',
        ]);
        $this->gerenciaProdutosService->atualizarProduto($id, array_merge($dados, ['imagem' => $request->file('imagem')]));
        return redirect()->route('gerenciamento_Produtos')->with('success', PassMensagens::ATUALIZADO_SUCESSO);
    }

    /**
     * Atualizar status do carrousel para um produto
     * Recebe: { no_carrousel: 0 ou 1 }
     */
    public function toggleCarrousel(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'no_carrousel' => 'required|boolean'
            ]);

            $produto = $this->gerenciaProdutosService->atualizarCarrousel(
                $id,
                (bool) $validated['no_carrousel']
            );

            $status = $validated['no_carrousel'] ? 'adicionado ao' : 'removido do';

            return response()->json([
                'success' => true,
                'message' => "Produto {$status} carrousel com sucesso!",
                'data' => [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'no_carrousel' => $produto->no_carrousel
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar carrousel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gerenciamentoBordas()
    {
        return view('Admin.GerenciamentoBordas', [
            'bordas' => BordaModel::query()->orderBy('nome')->get(),
            'usuario' => $this->genericBase->hasLogado(),
        ]);
    }

    public function cadastrarBorda(Request $request)
    {
        $dados = $request->validate($this->regrasBorda());
        $dados['adicionais_por_tamanho'] = $this->normalizarAdicionais($dados['adicionais_por_tamanho'] ?? []);
        $dados['ativa'] = $request->boolean('ativa');
        BordaModel::create($dados);
        $this->limparCacheProdutos();

        return redirect()->route('gerenciamento_bordas')->with('success', 'Borda cadastrada com sucesso.');
    }

    public function atualizarBorda(Request $request, int $id)
    {
        $dados = $request->validate($this->regrasBorda($id));
        $dados['adicionais_por_tamanho'] = $this->normalizarAdicionais($dados['adicionais_por_tamanho'] ?? []);
        $dados['ativa'] = $request->boolean('ativa');
        BordaModel::query()->findOrFail($id)->update($dados);
        $this->limparCacheProdutos();

        return redirect()->route('gerenciamento_bordas')->with('success', 'Borda atualizada com sucesso.');
    }

    private function regrasBorda(?int $id = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:100', Rule::unique('bordas', 'nome')->ignore($id)],
            'adicionais_por_tamanho' => 'nullable|array:P,M,G,GG,XG',
            'adicionais_por_tamanho.*' => 'nullable|numeric|min:0|max:99999',
            'ativa' => 'nullable|boolean',
        ];
    }

    private function normalizarAdicionais(array $adicionais): array
    {
        $resultado = [];
        foreach (['P', 'M', 'G', 'GG', 'XG'] as $tamanho) {
            $valor = $adicionais[$tamanho] ?? null;
            if ($valor !== null && $valor !== '') {
                $resultado[$tamanho] = number_format((float) str_replace(',', '.', (string) $valor), 2, '.', '');
            }
        }

        return $resultado;
    }

    private function limparCacheProdutos(): void
    {
        foreach (['lista_produtos', 'lista_produtos_destaque', 'todos_produtos', 'produtos_categorias', 'bordas_ativas'] as $chave) {
            Cache::forget($chave);
        }
    }
}

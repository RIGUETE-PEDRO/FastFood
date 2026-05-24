<?php
namespace App\Http\Controllers;

use App\Mensagens\PassMensagens;
use App\Models\ProdutoModel;
use App\Models\Categoria;
use App\Services\GerenciaProdutosService;
use App\Services\GenericBase;
use App\Services\AuthService;

use Illuminate\Http\Request;


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
        $this->gerenciaProdutosService->criarProduto($request);
        return redirect()->route('ListaProdutos')->with('success', PassMensagens::CADASTRAR_PRODUTO_SUCESSO);
    }

    public function deletarProduto($id)
    {
        $this->gerenciaProdutosService->removerProduto($id);
        return redirect()->route('gerenciamento_Produtos')->with('success', PassMensagens::DELETE_SUCESSO);
    }

    public function atualizarProduto(Request $request, $id)
    {
        $this->gerenciaProdutosService->atualizarProduto($id, $request->all());
        return redirect()->route('gerenciamento_Produtos')->with('success', PassMensagens::ATUALIZADO_SUCESSO);
    }

    /**
     * Atualizar status do carrousel para um produto
     * Recebe: { no_carrousel: 0 ou 1 }
     */
    public function toggleCarrousel(Request $request, $id)
    {
        try {
            $produto = ProdutoModel::findOrFail($id);

            // Validar o request
            $validated = $request->validate([
                'no_carrousel' => 'required|boolean'
            ]);

            // Atualizar o campo no_carrousel
            $produto->update([
                'no_carrousel' => $validated['no_carrousel']
            ]);

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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
}

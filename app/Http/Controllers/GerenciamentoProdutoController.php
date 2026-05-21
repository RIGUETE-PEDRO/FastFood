<?php
namespace App\Http\Controllers;

use App\Mensagens\PassMensagens;
use App\Models\ProdutoModel as Produto;
use App\Services\GerenciaProdutosService;
use App\Services\GenericBase;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;

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
        Log::info('Carrousel Toggle Iniciado', [
            'produto_id' => $id,
            'request_data' => $request->all()
        ]);

        try {
            $produto = Produto::findOrFail($id);

            // Validar o request - aceitar boolean ou integer (0/1)
            $validated = $request->validate([
                'no_carrousel' => 'required|in:0,1,true,false'
            ]);

            // Converter para booleano se for string
            $noCarrousel = filter_var($validated['no_carrousel'], FILTER_VALIDATE_BOOLEAN);

            Log::info('Validação OK', ['valor' => $noCarrousel]);

            // Atualizar o campo no_carrousel
            $produto->no_carrousel = $noCarrousel;
            $produto->save();

            Log::info('Produto Atualizado', [
                'id' => $produto->id,
                'no_carrousel' => $produto->no_carrousel
            ]);

            $status = $noCarrousel ? 'adicionado ao' : 'removido do';

            return response()->json([
                'success' => true,
                'message' => "Produto {$status} carrousel com sucesso!",
                'data' => [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'no_carrousel' => (bool)$produto->no_carrousel
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Produto não encontrado', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar carrousel', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar carrousel: ' . $e->getMessage()
            ], 500);
        }
    }
}

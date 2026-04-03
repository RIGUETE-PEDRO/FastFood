<?php
namespace App\Http\Controllers;

use App\Mensagens\PassMensagens;
use App\Models\Produto;
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
}

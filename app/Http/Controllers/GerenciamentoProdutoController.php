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


    public function gerenciamentoProduto()
    {

        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $nomeUsuario = $usuarioLogado ? explode(' ', trim($usuarioLogado->nome))[0] : 'Usuário';

        $produtos = Produto::with('categoria')->get(); 
        $categorias = Categoria::all();



        return view('Admin.GerenciamentoProduto', [
            'produtos' => $produtos,
            'categorias' => $categorias,
            'usuario' => $usuarioLogado,
            'nomeUsuario' => $nomeUsuario,
        ]);
    }


    public function cadastrarProduto(Request $request)
    {
        $gerenciaProdutosService = new GerenciaProdutosService();
        $gerenciaProdutosService->criarProduto($request);

        return redirect()->route('ListaProdutos')->with('success', PassMensagens::CADASTRAR_PRODUTO_SUCESSO);
    }

    public function deletarProduto($id)
    {



            $gerenciaProdutosService = new GerenciaProdutosService();
            $gerenciaProdutosService->removerProduto($id);


        return redirect()->route('gerenciamento_Produtos')->with('success', PassMensagens::DELETE_SUCESSO);
    }

    public function atualizarProduto(Request $request, $id)
    {


        $gerenciaProdutosService = new GerenciaProdutosService();
        $gerenciaProdutosService->atualizarProduto($id, $request->all());

        return redirect()->route('gerenciamento_Produtos')->with('success', PassMensagens::ATUALIZADO_SUCESSO);
    }

}

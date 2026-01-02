<?php
namespace App\Http\Controllers;
use App\Models\Produto;
use App\Models\Categoria;
use App\Services\GerenciaProdutosService;
use App\Services\GenericBase;

use Illuminate\Http\Request;


class GerenciamentoProdutoController extends Controller
{


    public function gerenciamentoProduto()
    {
        $produtos = Produto::with('categoria')->get(); // Busca todos os produtos com a categoria relacionada
        $categorias = Categoria::all(); // Busca todas as categorias para o select

        // Se você usa autenticação e quer passar o usuário/nome:
        $usuario = session('usuario_logado');
        $nomeUsuario = $usuario ? explode(' ', trim($usuario->nome))[0] : 'Usuário';

        return view('Admin.GerenciamentoProduto', [
            'produtos' => $produtos,
            'categorias' => $categorias,
            'usuario' => $usuario,
            'nomeUsuario' => $nomeUsuario,
        ]);
    }


    public function cadastrarProduto(Request $request)
    {
        $gerenciaProdutosService = new GerenciaProdutosService();
        $gerenciaProdutosService->criarProduto($request);

        return redirect()->route('ListaProdutos')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function deletarProduto($id)
    {



            $gerenciaProdutosService = new GerenciaProdutosService();
            $gerenciaProdutosService->removerProduto($id);
        

        return redirect()->route('gerenciamento_Produtos')->with('success', 'Produto deletado com sucesso!');
    }

    public function atualizarProduto(Request $request, $id)
    {


        $gerenciaProdutosService = new GerenciaProdutosService();
        $gerenciaProdutosService->atualizarProduto($id, $request->all());

        return redirect()->route('gerenciamento_Produtos')->with('success', 'Produto atualizado com sucesso!');
    }

}

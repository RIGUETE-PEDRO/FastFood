<?php

namespace App\Http\Controllers;

use App\Services\GarcomService;
use App\Services\GerenciaProdutosService;
use App\Services\MesasService;
use Illuminate\Http\Request;

class GarcomController extends Controller
{
    public function __construct(private GarcomService $garcomService, private GerenciaProdutosService $gerenciaProdutosService, private MesasService $mesasService)
    {
        $this->gerenciaProdutosService = $gerenciaProdutosService;
        $this->mesasService = $mesasService;
    }

    public function index()
    {
        $dados = $this->gerenciaProdutosService->gerenciarProdutos();
        $mesas = $this->mesasService->pegarMesas();
        return view('Admin.Garcom', [
            'produtos' => $dados['produtos'] ?? [],
            'categorias' => $dados['categorias'] ?? [],
            'usuario' => $dados['usuarioLogado'] ?? null,
            'nomeUsuario' => $dados['nomeUsuario'] ?? 'Usuário',
            'mesas' => $mesas,
        ]);
    }

    public function adicionarProduto(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|integer|exists:produtos,id',
            'mesa_id' => 'required|integer|exists:mesas,id',
            'quantidade' => 'required|integer|min:1',
        ]);

        $this->garcomService->adicionarAoPedido($request);

        return back()->with('success', 'Produto adicionado ao pedido com sucesso.');
    }





}

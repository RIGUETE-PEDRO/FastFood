<?php

namespace App\Http\Controllers;

use App\Services\GarcomService;
use App\Services\GerenciaProdutosService;
use App\Services\MesasService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $pizzas = collect($dados['produtos'] ?? [])
            ->filter(function ($produto) {
                $categoria = mb_strtolower(Str::ascii(trim((string) optional($produto->categoria)->nome)));
                return $produto->disponivel && in_array($categoria, ['pizza', 'pizzas'], true);
            })
            ->values();

        return view('Admin.Garcom', [
            'produtos' => $dados['produtos'] ?? [],
            'categorias' => $dados['categorias'] ?? [],
            'pizzas' => $pizzas,
            'usuario' => $dados['usuarioLogado'] ?? null,
            'nomeUsuario' => $dados['nomeUsuario'] ?? 'Usuário',
            'mesas' => $mesas,
        ]);
    }

    public function adicionarProduto(Request $request)
    {
        if ($request->has('itens')) {
            $request->validate([
                'mesa_id' => 'required|integer|exists:mesas,id',
                'itens' => 'required|array|min:1',
                'itens.*' => 'array',
                'itens.*.produto_id' => 'required|integer|exists:produtos,id',
                'itens.*.quantidade' => 'required|integer|min:1|max:99',
                'itens.*.observacao' => 'nullable|string|max:500',
                'itens.*.personalizacao' => 'nullable|array',
                'itens.*.personalizacao.tipo' => 'nullable|in:pizza,porcao',
                'itens.*.personalizacao.modo' => 'nullable|in:inteira,meia_a_meia',
                'itens.*.personalizacao.tamanho' => 'nullable|in:P,M,G,GG,XG',
                'itens.*.personalizacao.borda' => 'nullable|in:sem_borda,com_borda',
                'itens.*.personalizacao.borda_id' => 'nullable|integer|min:0',
                'itens.*.personalizacao.sabor_2_id' => 'nullable|integer|min:1',
                'itens.*.personalizacao.tipo_porcao' => 'nullable|in:inteira,meia',
            ]);

            try {
                $this->garcomService->adicionarAoPedido($request);
            } catch (\InvalidArgumentException $e) {
                return back()->withInput()->withErrors(['itens' => $e->getMessage()]);
            }

            return back()->with('success', 'Lista adicionada a mesa com sucesso.');
        }

        $request->validate([
            'produto_id' => 'required|integer|exists:produtos,id',
            'mesa_id' => 'required|integer|exists:mesas,id',
            'quantidade' => 'required|integer|min:1|max:99',
            'observacao' => 'nullable|string|max:500',
            'personalizacao' => 'nullable|array',
            'personalizacao.tipo' => 'nullable|in:pizza,porcao',
            'personalizacao.modo' => 'nullable|in:inteira,meia_a_meia',
            'personalizacao.tamanho' => 'nullable|in:P,M,G,GG,XG',
            'personalizacao.borda' => 'nullable|in:sem_borda,com_borda',
            'personalizacao.borda_id' => 'nullable|integer|min:0',
            'personalizacao.sabor_2_id' => 'nullable|integer|min:1',
            'personalizacao.tipo_porcao' => 'nullable|in:inteira,meia',
        ]);

        try {
            $this->garcomService->adicionarAoPedido($request);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['personalizacao' => $e->getMessage()]);
        }

        return back()->with('success', 'Produto adicionado ao pedido com sucesso.');
    }





}

<?php

namespace App\Http\Controllers;
use App\Services\IndexService;
use Illuminate\Http\Request;
use App\Services\GenericBase;

class IndexController extends Controller
{
    protected GenericBase $genericBase;
    protected IndexService $indexService;

    public function __construct(GenericBase $genericBase, IndexService $indexService)
    {
        $this->genericBase = $genericBase;
        $this->indexService = $indexService;
    }

    public function index()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $produtos = $this->indexService->pegarProdutosIndex();
        $produtosDestaque = $this->indexService->pegarProdutosDestaque();
        
        return view('Index', [
            'usuario' => $usuarioLogado,
            'produtos' => $produtos,
            'produtosDestaque' => $produtosDestaque,
        ]);

    }
}

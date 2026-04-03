<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GenericBase;

class IndexController extends Controller
{
    protected GenericBase $genericBase;

    public function __construct(GenericBase $genericBase)
    {
        $this->genericBase = $genericBase;
    }

    public function index()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $produtos = $this->genericBase->pegarProdutos();

        return view('Index', ['usuario' => $usuarioLogado, 'produtos' => $produtos]);

    }
}

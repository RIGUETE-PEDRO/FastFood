<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GenericBase;

class IndexController extends Controller
{
    public function index()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();

        $produtos = $genericBase->pegarProdutos();

        return view('Index', ['usuario' => $usuarioLogado, 'produtos' => $produtos]);

    }
}

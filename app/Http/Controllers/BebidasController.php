<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;

class BebidasController extends Controller
{
    public function Bebidas()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $bebidas = $genericBase->findByProdutos('Bebidas');

        return view('Bebida', [
            'usuario' => $usuarioLogado,
            'bebidas' => $bebidas,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;

class BebidasController extends Controller
{
    protected GenericBase $genericBase;

    public function __construct(GenericBase $genericBase)
    {
        $this->genericBase = $genericBase;
    }

    public function Bebidas()
    {
        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        $bebidas = $this->genericBase->findByProdutos('Bebidas');

        return view('Bebida', [
            'usuario' => $usuarioLogado,
            'bebidas' => $bebidas,
        ]);
    }
}

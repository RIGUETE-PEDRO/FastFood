<?php

namespace App\Http\Controllers;

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
        $usuarioLogado =  $this->genericBase->hasLogado();
        $bebidas = $this->genericBase->findByProdutos('Bebidas');

        return view('Bebida', [
            'usuario' => $usuarioLogado,
            'bebidas' => $bebidas,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;

class LanchesController extends Controller
{
    protected GenericBase $genericBase;

    public function __construct(GenericBase $genericBase)
    {
        $this->genericBase = $genericBase;
    }

    public function Lanches()
    {

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        $lanches = $this->genericBase->findByProdutos('Lanches');

        return view('Lanches', [
            'usuario' => $usuarioLogado,
            'lanches' => $lanches,
        ]);

    }
}

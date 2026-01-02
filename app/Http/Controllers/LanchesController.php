<?php

namespace App\Http\Controllers;

use App\Services\GenericBase;

class LanchesController extends Controller
{
    public function Lanches()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $lanches = $genericBase->findByProdutos('Lanches');

        return view('Lanches', [
            'usuario' => $usuarioLogado,
            'lanches' => $lanches,
        ]);

    }
}

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GenericBase;

class PorcaoController extends Controller
{
    public function porcao()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $porcao = $genericBase->findByProdutos('Porcao');

        return view('Porcao', [
            'usuario' => $usuarioLogado,
            'porcao' => $porcao,
        ]);
    }
}

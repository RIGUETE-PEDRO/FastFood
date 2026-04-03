<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GenericBase;

class PorcaoController extends Controller
{

    protected GenericBase $genericBase;

    public function __construct(GenericBase $genericBase)
    {
        $this->genericBase = $genericBase;
    }
    public function porcao()
    {

        $usuarioLogado =  $this->genericBase->hasLogado();
        $porcao = $this->genericBase->findByProdutos('Porções');

        return view('Porcao', [
            'usuario' => $usuarioLogado,
            'porcao' => $porcao,
        ]);
    }
}

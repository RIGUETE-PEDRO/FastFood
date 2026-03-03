<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;

class PizzaController extends Controller
{
    protected GenericBase $genericBase;

    public function __construct(GenericBase $genericBase)
    {
        $this->genericBase = $genericBase;
    }

    public function Pizza()
    {

        $usuarioLogado = $this->genericBase->pegarUsuarioLogado();
        $Pizzas = $this->genericBase->findByProdutos('Pizzas');

        return view('Pizza', [
            'usuario' => $usuarioLogado,
            'pizzas' => $Pizzas,
        ]);
    }
}

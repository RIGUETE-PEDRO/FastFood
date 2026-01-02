<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericBase;

class PizzaController extends Controller
{
    public function Pizza()
    {
        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $Pizzas = $genericBase->findByProdutos('Pizzas');

        return view('Pizza', [
            'usuario' => $usuarioLogado,
            'pizzas' => $Pizzas,
        ]);
    }
}

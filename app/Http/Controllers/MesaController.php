<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Services\GenericBase;
use App\Services\MesasService;
use Illuminate\Http\Request;


class MesaController extends Controller
{
    public function Mesa()
    {

        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();
        $mesasService = new MesasService();
        $mesas = $mesasService->pegarMesas();

        //carregar pedidos das messas

        return view('Admin.Mesa', ['usuario' => $usuarioLogado, 'mesas' => $mesas]);
    }

    public function cadastrarMesa(Request $request)
    {

        $mesasService = new MesasService();

        if ($request->input('numero_da_mesa') < 1) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
        }



        $mesasService->cadastrarMesa($request);
        return redirect()->back()->with('success', PassMensagens::MESA_CADASTRADA_SUCESSO);
    }

    public function ListarMesa(Request $request)
    {
        $mesasService = new MesasService();
        $mesas = $mesasService->pegarMesas();

        return view('Admin.Mesa', ['mesas' => $mesas]);
    }

    public function removerMesa(Request $request)
    {

        $id = $request->input('mesa_id');


        if (!$id) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }


        $mesasService = new MesasService();
        $mesasService->removerMesa($id);

        return redirect()->route('mesas.index')->with('success', PassMensagens::MESA_REMOVIDA_SUCESSO);
    }

    public function atualizarMesa(Request $request)
    {
        /*method nao implementado */
    }
}

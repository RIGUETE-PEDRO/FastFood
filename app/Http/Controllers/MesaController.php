<?php

namespace App\Http\Controllers;

use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;
use App\Models\FormaPagamento;
use App\Models\ItemPedido;
use App\Services\GenericBase;
use App\Services\MesasService;
use Illuminate\Http\Request;


class MesaController extends Controller
{
    protected GenericBase $genericBase;
    protected MesasService $mesasService;


    public function __construct(GenericBase $genericBase, MesasService $mesasService)
    {
        $this->genericBase = $genericBase;
        $this->mesasService = $mesasService;
    }


    public function Mesa()
    {

        $usuarioLogado =  $this->genericBase->hasLogado();
        $mesas = $this->mesasService->pegarMesas();

        return view('Admin.Mesa', ['usuario' => $usuarioLogado, 'mesas' => $mesas]);
    }

    public function cadastrarMesa(Request $request)
    {
        $response = $this->mesasService->cadastrarMesa($request);
        if ($response) {
            return $response;
        }

        return redirect()->back()->with('success', PassMensagens::MESA_CADASTRADA_SUCESSO);
    }

    public function ListarMesa()
    {
        $mesas = $this->mesasService->pegarMesas();
        return view('Admin.Mesa', ['mesas' => $mesas]);
    }

    public function removerMesa(Request $request)
    {

        $id = $request->input('mesa_id');


        if (!$id) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $this->mesasService->removerMesa($id);

        return redirect()->route('mesas.index')->with('success', PassMensagens::MESA_REMOVIDA_SUCESSO);
    }

    public function atualizarMesa(Request $request)
    {
        $response = $this->mesasService->atualizarMesa($request);
        if ($response) {
            return $response;
        }

        return redirect()->back()->with('success', PassMensagens::MESA_ATUALIZADA_SUCESSO);
    }

    public function detalhesMesa($id)
    {
        $usuarioLogado =  $this->genericBase->hasLogado();
        $detalhes = $this->mesasService->pegarDetalhesMesa($id);

        return view('Admin.DetalhesMesa', [
            'usuario' => $usuarioLogado,
            'mesa' => $detalhes['mesa'],
            'itensAbertos' => $detalhes['itensAbertos'],
            'itensPagos' => $detalhes['itensPagos'],
            'totalAberto' => $detalhes['totalAberto'],
            'formasPagamento' => $detalhes['formasPagamento'],
        ]);
    }

    public function abaterItensContaMesa(Request $request, $id)
    {
        $this->genericBase->hasLogado();

        $resultado = $this->mesasService->abaterItensContaMesa($request, $id);

        if (!$resultado['status']) {
            return redirect()->back()->with('error', $resultado['mensagem']);
        }

        return redirect()->back()->with('success', $resultado['mensagem']);
    }

    public function atualizarItemContaMesa(Request $request, int $id, int $itemId)
    {
        $this->genericBase->hasLogado();

        $resultado = $this->mesasService->atualizarItemContaMesa($request, $id, $itemId);

        if (!$resultado['status']) {
            return redirect()->back()->with('error', $resultado['mensagem']);
        }

        return redirect()->back()->with('success', $resultado['mensagem']);
    }

    public function removerItemContaMesa(int $id, int $itemId)
    {
        $this->genericBase->hasLogado();

        $resultado = $this->mesasService->removerItemContaMesa($id, $itemId);

        if (!$resultado['status']) {
            return redirect()->back()->with('error', $resultado['mensagem']);
        }

        return redirect()->back()->with('success', $resultado['mensagem']);
    }
}

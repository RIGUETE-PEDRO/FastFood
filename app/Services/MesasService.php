<?php

namespace App\Services;

use App\Models\Mesa;
use App\Mensagens\ErroMensagens;
use App\Models\Produto;
use Illuminate\Http\RedirectResponse;

class MesasService extends GenericBase
{
    public function pegarMesas()
    {
        return Mesa::all();
    }


    public function cadastrarMesa($request): ?RedirectResponse
    {
        if ($request->input('numero_da_mesa') < 1) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
        }

        if (Mesa::where('numero_da_mesa', $request->input('numero_da_mesa'))->exists()) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_JA_EXISTENTE);
        }

        $mesa = new Mesa();
        $mesa->numero_da_mesa = $request->input('numero_da_mesa');
        $mesa->status = $request->input('status') ?? 'disponivel';
        $mesa->preco = 0.00;
        $mesa->save();

        return null;
    }

    public function atualizarMesa($request): ?RedirectResponse
    {
        $id = $request->input('mesa_id');
        $mesa = Mesa::find($id);

        if (!$mesa) {
            return redirect()->back()->with('error', ErroMensagens::SEM_ID_MESA);
        }

        $novoNumero = $request->input('numero_da_mesa');
        $novoStatus = $request->input('status');

        if ($novoNumero !== null && $novoNumero !== '') {
            if ($novoNumero < 1) {
                return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
            }

            $numeroJaExiste = Mesa::where('numero_da_mesa', $novoNumero)
                ->where('id', '!=', $mesa->id)
                ->exists();
            if ($numeroJaExiste) {
                return redirect()->back()->with('error', ErroMensagens::NUMERO_JA_EXISTENTE);
            }

            $mesa->numero_da_mesa = $novoNumero;
        }

        if ($novoStatus !== null && $novoStatus !== '') {
            $mesa->status = $novoStatus;
        }
        $mesa->save();

        return null;
    }


    public function removerMesa($id){
        $mesa = Mesa::find($id);
        if ($mesa) {
            $mesa->delete();
        }
    }


    public function pegarProdutosMesa($id){
        return Produto::where('mesa_id', $id)->get();
    }
}

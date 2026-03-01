<?php

namespace App\Services;

use App\Models\Mesa;
use App\Mensagens\ErroMensagens;
use App\Mensagens\PassMensagens;

class MesasService extends GenericBase
{
    public function pegarMesas()
    {
        return Mesa::all();
    }


    public function cadastrarMesa($request){
        if ($request->input('numero_da_mesa') < 1) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_MESA_INVALIDO);
        }

        if ($request->input('numero_da_mesa') == (Mesa::where('numero_da_mesa', $request->input('numero_da_mesa'))->first())?->numero_da_mesa) {
            return redirect()->back()->with('error', ErroMensagens::NUMERO_JA_EXISTENTE);
        }

        $mesa = new Mesa();
        $mesa->numero_da_mesa = $request->input('numero_da_mesa');
        $mesa->status = $request->input('status');
        $mesa->preco = 0.00;
        $mesa->save();
    }


    public function removerMesa($id){
        $mesa = Mesa::find($id);
        if ($mesa) {
            $mesa->delete();
        }
    }
}

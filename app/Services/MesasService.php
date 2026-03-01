<?php

namespace App\Services;

use App\Models\Mesa;

class MesasService extends GenericBase
{
    public function pegarMesas()
    {
        return Mesa::all();
    }


    public function cadastrarMesa($request){
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

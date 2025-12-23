<?php

namespace App\Services;
use App\Models\Usuario;


class GenericBase
{

    public function findById(int $id)
    {
        return Usuario::find($id);
    }

    public function findAll()
    {
        return Usuario::all();
    }

    public function alterar(Usuario $usuario, array $dados)
    {
        $usuario->update($dados);
        return $usuario;
    }

}

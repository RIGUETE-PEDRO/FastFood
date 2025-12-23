<?php

namespace App\Services;
use App\Models\Usuario;
use App\Models\Funcionario;


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

    public function findFuncionarios()
    {
        // Busca da tabela funcionario com o relacionamento usuario
        return Funcionario::with('usuario')->get();
    }

    public function alterar(Usuario $usuario, array $dados)
    {
        $usuario->update($dados);
        return $usuario;
    }

    public function gerarNumero()
    {
        return random_int(1, 1000);
    }

    public function deletar(Usuario $usuario)
    {
        return $usuario->delete();
    }


    public function listagem($rota)
    {

        $lista = $this->findAll();

        return view($rota, [
            'lista' => $lista,
        ]);
    }






}

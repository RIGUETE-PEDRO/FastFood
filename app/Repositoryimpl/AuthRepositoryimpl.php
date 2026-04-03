<?php

namespace App\Repositoryimpl;

use App\Models\FuncionarioModel;
use App\Models\UsuarioModel;

class AuthRepositoryimpl
{
    public function criarUsuario(array $dados): UsuarioModel
    {
        return UsuarioModel::create($dados);
    }

    public function criarFuncionario(array $dados): FuncionarioModel
    {
        return FuncionarioModel::create($dados);
    }
}

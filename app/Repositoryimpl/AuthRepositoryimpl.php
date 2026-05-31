<?php

namespace App\Repositoryimpl;

use App\Models\FuncionarioModel;
use App\Models\UsuarioModel;


use App\Repository\AuthRepository;

class AuthRepositoryimpl implements AuthRepository
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

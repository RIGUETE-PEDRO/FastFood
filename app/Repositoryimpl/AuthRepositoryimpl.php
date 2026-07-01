<?php

namespace App\Repositoryimpl;

use App\Models\FuncionarioModel;
use App\Models\UsuarioModel;
use Illuminate\Support\Facades\Cache;

use App\Repository\AuthRepository;

class AuthRepositoryimpl implements AuthRepository
{
    public function criarUsuario(array $dados): UsuarioModel
    {
        $usuario = UsuarioModel::create($dados);
        return $usuario;
    }

    public function criarFuncionario(array $dados): FuncionarioModel
    {
        
        $funcionario = FuncionarioModel::create($dados);

        Cache::forget('List_funcionario');

        return $funcionario;

    }
}

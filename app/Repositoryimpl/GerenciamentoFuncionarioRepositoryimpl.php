<?php

namespace App\Repositoryimpl;

use App\Models\FuncionarioModel;

class GerenciamentoFuncionarioRepositoryimpl
{
    public function buscarFuncionarioPorUsuarioId(int $usuarioId): ?FuncionarioModel
    {
        return FuncionarioModel::where('usuario_id', $usuarioId)->first();
    }
}

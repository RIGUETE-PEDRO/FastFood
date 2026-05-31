<?php

namespace App\Repositoryimpl;

use App\Models\FuncionarioModel;
use App\Repository\GerenciamentoFuncionarioRepository;

class GerenciamentoFuncionarioRepositoryimpl implements GerenciamentoFuncionarioRepository
{
    public function buscarFuncionarioPorUsuarioId(int $usuarioId): ?FuncionarioModel
    {
        return FuncionarioModel::where('usuario_id', $usuarioId)->first();
    }
}

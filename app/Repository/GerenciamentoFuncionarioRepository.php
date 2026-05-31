<?php

namespace App\Repository;

interface GerenciamentoFuncionarioRepository
{

    public function buscarFuncionarioPorUsuarioId(int $usuarioId);

}

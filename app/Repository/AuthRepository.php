<?php

namespace App\Repository;


interface AuthRepository
{
    public function criarUsuario(array $dados);

    public function criarFuncionario(array $dados);
    
}

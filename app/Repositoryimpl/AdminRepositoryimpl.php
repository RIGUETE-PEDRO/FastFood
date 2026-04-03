<?php

namespace App\Repositoryimpl;

use App\Models\Funcionario;

class AdminRepositoryimpl
{
    public function buscarFuncionarios($searchTerm)
    {
        $query = Funcionario::with('usuario');

        if (!empty($searchTerm)) {
            $query->whereHas('usuario', function ($q) use ($searchTerm) {
                $q->where('nome', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        return $query->get();
    }
}

<?php

namespace App\Repositoryimpl;


use App\Models\FuncionarioModel;

class AdminRepositoryimpl
{
    public function buscarFuncionarios($searchTerm)
    {
        $query = FuncionarioModel::with('usuario');

        if (!empty($searchTerm)) {
            $query->whereHas('usuario', function ($q) use ($searchTerm) {
                $q->where('nome', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        return $query->get();
    }
}

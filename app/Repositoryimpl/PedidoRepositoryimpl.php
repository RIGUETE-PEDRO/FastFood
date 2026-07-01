<?php

namespace App\Repositoryimpl;

use App\Models\Dados_empresa;
use App\Models\PedidoModel;
use App\Repository\PedidoRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PedidoRepositoryimpl implements PedidoRepository
{
    public function filtrarPedidosDataNome($data, $nome): Collection
    {
        return PedidoModel::query()

            ->when($data, function ($query) use ($data) {
                $query->whereDate('created_at', $data);
            })

            ->when($nome, function ($query) use ($nome) {
                $query->whereHas('usuario', function ($q) use ($nome) {
                    $q->where('nome', 'like', "%{$nome}%");
                });
            })

            ->latest()
            ->get();
    }
    public function listarParaChecksum() : Collection
    {
        return PedidoModel::query()
            ->select(['id', 'status', 'updated_at'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function pegarPedidosDoUsuario(int $usuarioId) :Collection
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
        ])
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function buscarDadosEmpresa() :Collection 
    {
         return Cache::remember('dados_empresa', now()->addDays(30), function () {
            return Dados_empresa::all()
                ->pluck('Valor', 'Informacao');
         });
    }

}

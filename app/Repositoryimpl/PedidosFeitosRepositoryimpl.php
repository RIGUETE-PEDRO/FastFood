<?php

namespace App\Repositoryimpl;

use App\Enum\StatusPedidos;
use App\Models\Dados_empresa;
use App\Models\ItemPedidoModel;
use App\Models\MesaModel;
use App\Models\PedidoModel;
use App\Repository\PedidosFeitosRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PedidosFeitosRepositoryimpl implements PedidosFeitosRepository
{
    public function listarPedidos(): Collection
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
            'mesa',
            'motoboy',
        ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function salvarStatus(PedidoModel $pedido, int $status): PedidoModel
    {
        $pedido->status = $status;
        $pedido->save();

        $this->atualizarResumoDaMesa($pedido);

        return $pedido->fresh([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
            'mesa',
            'motoboy',
        ]);
    }

    /**
     * Mantém o saldo da mesa com pedidos pendentes e aceitos; cancelados ficam fora.
     */
    private function atualizarResumoDaMesa(PedidoModel $pedido): void
    {
        if (!$pedido->mesa_id) {
            return;
        }

        $mesa = MesaModel::find($pedido->mesa_id);
        if (!$mesa) {
            return;
        }

        $totalAberto = (float) ItemPedidoModel::query()
            ->whereHas('pedido', function ($query) use ($pedido) {
                $query->where('mesa_id', $pedido->mesa_id)
                    ->whereIn('status', [
                        StatusPedidos::PENDENTE->value,
                        StatusPedidos::EM_PREPARO->value,
                        StatusPedidos::A_CAMINHO->value,
                        StatusPedidos::ENTREGUE->value,
                    ]);
            })
            ->where('status_da_comanda', 'em_aberto')
            ->selectRaw('COALESCE(SUM(GREATEST(preco_unitario * quantidade - COALESCE(valor_pago, 0), 0)), 0) as total')
            ->value('total');

        $mesa->preco = $totalAberto;
        $mesa->status = $totalAberto > 0 ? 'Ocupada' : 'Disponível';
        $mesa->save();
    }

    public function buscarPedidoPorId($pedidoId): ?PedidoModel
    {
        return PedidoModel::with([
            'statusRelacionamento',
            'endereco.cidade',
            'itens.produto',
            'formaPagamento',
            'usuario',
            'mesa',
            'motoboy',
        ])->find($pedidoId);
    }

    public function buscarDadosEmpresa(): Collection
    {
        return Cache::remember('dados_empresa', now()->addDays(30), function () {
            return Dados_empresa::all()
                ->pluck('Valor', 'Informacao');
        });
    }
}

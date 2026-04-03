<?php

namespace App\Repositoryimpl;


use App\Enum\StatusPedidos;
use App\Models\PedidoModel;
use App\Models\FuncionarioModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function totalVendasNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): float
    {
        return (float) PedidoModel::query()
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->where('status', StatusPedidos::ENTREGUE->value)
            ->sum('valor_total');
    }

    public function totalPedidosNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): int
    {
        return (int) PedidoModel::query()
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->count();
    }

    public function contagemStatusNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): Collection
    {
        return PedidoModel::query()
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function produtoMaisVendidoNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): ?object
    {
        return DB::table('item_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->leftJoin('produtos as pr', 'pr.id', '=', 'ip.produto_id')
            ->whereBetween('p.created_at', [$inicioPeriodo, $fimPeriodo])
            ->select('ip.produto_id', 'pr.nome as produto_nome', DB::raw('SUM(ip.quantidade) as total_qtd'))
            ->groupBy('ip.produto_id', 'pr.nome')
            ->orderByDesc('total_qtd')
            ->first();
    }

    public function topProdutosNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo, int $limite = 5): Collection
    {
        return DB::table('item_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->leftJoin('produtos as pr', 'pr.id', '=', 'ip.produto_id')
            ->whereBetween('p.created_at', [$inicioPeriodo, $fimPeriodo])
            ->select('ip.produto_id', 'pr.nome as produto_nome', DB::raw('SUM(ip.quantidade) as total_qtd'))
            ->groupBy('ip.produto_id', 'pr.nome')
            ->orderByDesc('total_qtd')
            ->limit($limite)
            ->get();
    }
}

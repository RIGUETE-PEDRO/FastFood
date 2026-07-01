<?php

namespace App\Repositoryimpl;


use App\Enum\StatusPedidos;
use App\Models\Dados_empresa;
use App\Models\PedidoModel;
use App\Models\FuncionarioModel;
use App\Repository\AdminRepository;
use Carbon\Carbon;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminRepositoryimpl implements AdminRepository
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

    public function listarDadosEmpresa(): Collection
    {
        //cache do redis para armazenar os dados da empresa por 60 minutos
        return Cache::remember('dados_empresa', now()->addMinutes(60), function () {
            return Dados_empresa::query()
                ->orderBy('id')
                ->get();
        });
    }

    //pegar o valor de uma informação da empresa e dropar o cache do listar dados empresa
    public function atualizarDadoEmpresa(string $informacao, ?string $valor): void
    {
        Dados_empresa::query()
            ->where('Informacao', $informacao)
            ->update(['Valor' => $valor]);
        
        Cache::forget('dados_empresa');
    }

    public function totalVendasNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): float
    {
        return (float) PedidoModel::query()
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->where('status', '!=', StatusPedidos::PENDENTE->value)
            ->where('status', '!=', StatusPedidos::CANCELADO->value)
            ->sum('valor_total');
    }

    public function totalPedidosNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): int
    {
        return (int) DB::table('item_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->whereBetween('p.created_at', [$inicioPeriodo, $fimPeriodo])
            ->whereNotIn('p.status', [
                StatusPedidos::PENDENTE->value,
                StatusPedidos::CANCELADO->value,
            ])
            ->sum('ip.quantidade');
    }

    public function contagemStatusNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): Collection
    {
        return PedidoModel::query()
            ->whereBetween('created_at', [$inicioPeriodo, $fimPeriodo])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    //pegar o produto mais vendido no periodo e retornar o nome do produto e a quantidade vendida
    public function produtoMaisVendidoNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo): ?object
    {
        return DB::table('item_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->leftJoin('produtos as pr', 'pr.id', '=', 'ip.produto_id')
            ->whereBetween('p.created_at', [$inicioPeriodo, $fimPeriodo])
            ->where('p.status', '!=', StatusPedidos::PENDENTE->value)
            ->select('ip.produto_id', 'pr.nome as produto_nome', DB::raw('SUM(ip.quantidade) as total_qtd'))
            ->groupBy('ip.produto_id', 'pr.nome')
            ->orderByDesc('total_qtd')
            ->first();
    }

    //pegar os top produtos mais vendidos no periodo e retornar o nome do produto e a quantidade vendida
    public function topProdutosNoPeriodo(Carbon $inicioPeriodo, Carbon $fimPeriodo, int $limite = 5): Collection
    {
        return DB::table('item_pedido as ip')
            ->join('pedidos as p', 'p.id', '=', 'ip.pedido_id')
            ->leftJoin('produtos as pr', 'pr.id', '=', 'ip.produto_id')
            ->whereBetween('p.created_at', [$inicioPeriodo, $fimPeriodo])
            ->where('p.status', '!=', StatusPedidos::PENDENTE->value)
            ->select('ip.produto_id', 'pr.nome as produto_nome', DB::raw('SUM(ip.quantidade) as total_qtd'))
            ->groupBy('ip.produto_id', 'pr.nome')
            ->orderByDesc('total_qtd')
            ->limit($limite)
            ->get();
    }
}

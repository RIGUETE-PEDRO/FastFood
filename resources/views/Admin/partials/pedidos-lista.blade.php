@if($pedidos->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="titulo-card">Nenhum pedido no momento</h2>
            <p class="texto-suave mb-0">Assim que os clientes realizarem pedidos eles aparecerão aqui.</p>
        </div>
    </div>
@else
    <section class="lista-pedidos-admin">
        <h2 class="secao-titulo">Pedidos em andamento</h2>
        @forelse(($pedidosPorStatus['abertos'] ?? collect()) as $pedido)
            @include('Admin.partials.pedido-card', [
                'pedido' => $pedido,
                'statusOptions' => $statusOptions,
                'statusTimeline' => $statusTimeline,
                'statusLabels' => $statusLabels,
                'desabilitarAcoes' => false,
            ])
        @empty
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <p class="texto-suave mb-0">Nenhum pedido em preparo ou a caminho agora.</p>
                </div>
            </div>
        @endforelse
    </section>

    <section class="acordeao-pedidos">
        <button class="acordeao-pedidos__gatilho" type="button" data-target="#pedidosFinalizados" aria-controls="pedidosFinalizados" aria-expanded="false">
            <span>Pedidos finalizados</span>
            <span class="acordeao-pedidos__contador">{{ count($pedidosPorStatus['finalizados'] ?? []) }}</span>
            <span class="acordeao-pedidos__icone" aria-hidden="true"></span>
        </button>
        <div class="acordeao-pedidos__conteudo" id="pedidosFinalizados" hidden>
            @forelse(($pedidosPorStatus['finalizados'] ?? collect()) as $pedido)
                @include('Admin.partials.pedido-card', [
                    'pedido' => $pedido,
                    'statusOptions' => $statusOptions,
                    'statusTimeline' => $statusTimeline,
                    'statusLabels' => $statusLabels,
                    'desabilitarAcoes' => true,
                    'colapsavel' => true,
                    'iniciarRecolhido' => true,
                ])
            @empty
                <div class="card shadow-sm">
                    <div class="card-body text-center py-4">
                        <p class="texto-suave mb-0">Nenhum pedido entregue ou cancelado ainda.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endif

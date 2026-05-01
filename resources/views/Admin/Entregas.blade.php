<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Entregas</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Pedidos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Entregas.css') }}">
</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>


            <main class="container py-4 pedidos-admin">
                <header class="cabecalho-pedidos mb-4">
                    <div class="cabecalho-pedidos__titulo">
                        <span class="cabecalho-pedidos__etiqueta">Entregas</span>
                        <h1 class="titulo-pagina">Olá, {{ $nomeUsuario ?? 'Entregador' }}!</h1>
                        <p class="texto-suave mb-0">Veja os pedidos com nome do cliente e endereço para entrega.</p>
                    </div>
                    <div class="cabecalho-pedidos__dados">
                        <span class="badge-total">{{ $totalPedidosEntrega ?? 0 }} pedidos de entrega</span>
                    </div>
                </header>

                <section class="lista-pedidos-admin">
                    <h2 class="secao-titulo secao-titulo--disponiveis">Pedidos disponíveis para atribuir</h2>

                    @forelse(($pedidosAbertos ?? collect()) as $pedido)
                    @php
                    $statusValor = (int) ($pedido->status_enum->value ?? $pedido->status ?? 0);
                    $statusClasse = match ($statusValor) {
                    1 => 'badge-status badge-status--pendente',
                    2 => 'badge-status badge-status--preparo',
                    3 => 'badge-status badge-status--padrao',
                    4 => 'badge-status badge-status--entregue',
                    5 => 'badge-status badge-status--cancelado',
                    default => 'badge-status badge-status--padrao',
                    };
                    @endphp

                    <article class="pedido-card entregas-card" data-status="{{ $statusValor }}">
                        <header class="pedido-card__header">
                            <div>
                                <h3 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h3>
                                <span class="pedido-card__subtitulo">{{ optional($pedido->created_at)->format('d/m/Y \à\s H:i') ?? 'N/D' }}</span>
                            </div>
                            <span class="{{ $statusClasse }}">{{ strtoupper((string) ($pedido->status_label ?? '')) }}</span>
                        </header>

                        <section class="pedido-card__secao">
                            <h4 class="pedido-card__secao-titulo">Cliente</h4>
                            <p class="mb-1"><strong>Nome:</strong> {{ optional($pedido->usuario)->nome ?? 'Não informado' }}</p>
                            <p class="mb-0"><strong>Telefone:</strong> {{ optional($pedido->usuario)->telefone ?? 'Não informado' }}</p>
                        </section>

                        <section class="pedido-card__secao">
                            <h4 class="pedido-card__secao-titulo">Endereço de entrega</h4>
                            <p class="mb-1"><strong>Logradouro:</strong> {{ optional($pedido->endereco)->logradouro ?? 'Não informado' }}</p>
                            <p class="mb-1"><strong>Número:</strong> {{ optional($pedido->endereco)->numero ?? 's/n' }}</p>
                            <p class="mb-1"><strong>Bairro:</strong> {{ optional($pedido->endereco)->bairro ?? 'Não informado' }}</p>
                            <p class="mb-1"><strong>Complemento:</strong> {{ optional($pedido->endereco)->complemento ?: '—' }}</p>
                            <p class="mb-0"><strong>Cidade:</strong> {{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado' }}</p>
                        </section>

                        <section class="pedido-card__secao">
                            <h4 class="pedido-card__secao-titulo">Motoboy</h4>
                            @php
                            $motoboyAtualId = (int) (optional($usuario)->id ?? 0);
                            $motoboyPedidoId = (int) (optional($pedido)->motoboy_id ?? 0);
                            $statusPedido = (int) (optional($pedido)->status ?? 0);
                            $etapaEntrega = $statusPedido === 3;
                            @endphp

                            @if ($motoboyPedidoId === 0 && $etapaEntrega)
                            <form action="{{ route('entregas.aceitar', $pedido->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-add">Aceitar entrega</button>
                            </form>
                            @elseif ($motoboyPedidoId === 0 && !$etapaEntrega)
                            <p class="mb-0"><strong>Apenas visualização.</strong> Aguarde o pedido entrar na etapa de entrega.</p>
                            @elseif ($motoboyPedidoId === $motoboyAtualId)
                            <p class="mb-2"><strong>Vinculado a você.</strong></p>
                            @if ($etapaEntrega)
                            <form action="{{ route('entregas.finalizar', $pedido->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-add">Finalizar entrega</button>
                            </form>
                            @else
                            <p class="mb-0">Aguardando status de entrega para finalizar.</p>
                            @endif
                            @else
                            <p class="mb-0">
                                <strong>Já vinculado:</strong>
                                {{ optional($pedido->motoboy)->nome ?? 'Outro motoboy' }}
                            </p>
                            @endif
                        </section>
                    </article>
                    @empty
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-4">
                            <p class="texto-suave mb-0">Nenhum pedido de entrega em aberto no momento.</p>
                        </div>
                    </div>
                    @endforelse
                </section>

                <section class="lista-pedidos-admin mt-4">
                    <h2 class="secao-titulo secao-titulo--aceitos">Pedidos aceitos</h2>

                    @forelse(($pedidosAceitos ?? collect()) as $pedido)
                    <article class="pedido-card entregas-card" data-status="{{ (int) ($pedido->status ?? 0) }}">
                        <header class="pedido-card__header">
                            <div>
                                <h3 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h3>
                                <span class="pedido-card__subtitulo">
                                    Cliente: {{ optional($pedido->usuario)->nome ?? 'Não informado' }}
                                </span>
                            </div>
                            <span class="badge-status badge-status--padrao">
                                {{ strtoupper((string) ($pedido->status_label ?? '')) }}
                            </span>
                        </header>
                        <p class="mb-0">
                            <strong>Motoboy:</strong>
                            {{ optional($pedido->motoboy)->nome ?? 'Não vinculado' }}
                        </p>
                        <p class="mb-0">
                            <strong>Endereço:</strong>
                            {{ optional($pedido->endereco)->logradouro ?? 'Não informado' }},
                            {{ optional($pedido->endereco)->numero ?? 's/n' }},
                            {{ optional($pedido->endereco)->bairro ?? 'Não informado' }},
                            {{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado' }}
                        </p>



                        @php
                        $motoboyAtualId = (int) (optional($usuario)->id ?? 0);
                        $motoboyPedidoId = (int) (optional($pedido)->motoboy_id ?? 0);
                        $statusPedido = (int) (optional($pedido)->status ?? 0);
                        $etapaEntrega = $statusPedido === 3;
                        @endphp

                        @if ($motoboyPedidoId === $motoboyAtualId && $etapaEntrega)
                        <div class="mt-3">
                            <form action="{{ route('entregas.finalizar', $pedido->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-add">Finalizar entrega</button>
                            </form>
                        </div>
                        @endif
                    </article>
                    @empty
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-4">
                            <p class="texto-suave mb-0">Nenhum pedido aceito até o momento.</p>
                        </div>
                    </div>
                    @endforelse
                </section>

                <section class="acordeao-pedidos">
                    <button class="acordeao-pedidos__gatilho" type="button" data-target="#entregasFinalizadas" aria-controls="entregasFinalizadas" aria-expanded="false">
                        <span>Entregas finalizadas</span>
                        <span class="acordeao-pedidos__contador">{{ count($pedidosFinalizados ?? []) }}</span>
                        <span class="acordeao-pedidos__icone" aria-hidden="true"></span>
                    </button>
                    <div class="acordeao-pedidos__conteudo" id="entregasFinalizadas" hidden>
                        @forelse(($pedidosFinalizados ?? collect()) as $pedido)
                        <article class="pedido-card" data-status="{{ (int) ($pedido->status ?? 0) }}">
                            <header class="pedido-card__header">
                                <div>
                                    <h3 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h3>
                                    <span class="pedido-card__subtitulo"><strong>Cliente:</strong> {{ optional($pedido->usuario)->nome ?? 'Não informado' }}</span><br>
                                    <span class="pedido-card__subtitulo"><strong>Endereço:</strong> {{ optional($pedido->endereco)->logradouro ?? 'Não informado' }}, {{ optional($pedido->endereco)->numero ?? 's/n' }}, {{ optional($pedido->endereco)->bairro ?? 'Não informado' }}, {{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado' }}</span><br>
                                    <span class="pedido-card__subtitulo"><strong>Motoboy:</strong> {{ optional($pedido->motoboy)->nome ?? 'Não vinculado' }}</span>
                                    <span class="pedido-card__subtitulo"><strong>Finalizado:</strong> em: {{ optional($pedido->updated_at)->format('d/m/Y \à\s H:i') ?? 'N/D' }}</span>
                                </div>
                                <span class="badge-status badge-status--entregue">{{ strtoupper((string) ($pedido->status_label ?? 'FINALIZADO')) }}</span>
                            </header>
                        </article>
                        @empty
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-4">
                                <p class="texto-suave mb-0">Nenhuma entrega finalizada ainda.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>

    @include('components.flash-toast')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gatilho = document.querySelector('.acordeao-pedidos__gatilho');
            const conteudo = document.querySelector('#entregasFinalizadas');

            if (!gatilho || !conteudo) return;

            gatilho.addEventListener('click', function() {
                const aberto = gatilho.getAttribute('aria-expanded') === 'true';
                gatilho.setAttribute('aria-expanded', aberto ? 'false' : 'true');
                conteudo.hidden = aberto;
                conteudo.classList.toggle('is-open', !aberto);
            });
        });
    </script>
</body>

</html>

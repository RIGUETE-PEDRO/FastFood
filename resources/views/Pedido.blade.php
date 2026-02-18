<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pedidos</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Pedido.css') }}">
</head>

<body>

    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
    <main>
        <div class="container mt-4 conteinner-pedidos">
            <h1>Meus Pedidos</h1>

            @if($pedidos->isEmpty())
            <p class="text-muted">Você não possui pedidos.</p>
            @else
            <div class="lista-pedidos mt-3">
                @foreach($pedidos as $pedido)
                @php
                $statusTexto = strtoupper((string) optional($pedido->statusRelacionamento)->status);
                $statusClasse = match ($statusTexto) {
                'PENDENTE' => 'badge-status badge-status--pendente',
                'EM PREPARO', 'PREPARANDO' => 'badge-status badge-status--preparo',
                'ENTREGUE' => 'badge-status badge-status--entregue',
                'CANCELADO' => 'badge-status badge-status--cancelado',
                default => 'badge-status badge-status--padrao',
                };
                @endphp

                <article class="pedido-card">
                    <header class="pedido-card__header">
                        <div>
                            <h2 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h2>
                            <span class="pedido-card__subtitulo">Realizado em {{ optional($pedido->created_at)->format('d/m/Y \à\s H:i') ?? 'N/D' }}</span>
                        </div>
                        <span class="{{ $statusClasse }}">{{ $statusTexto !== '' ? $statusTexto : 'STATUS INDEFINIDO' }}</span>
                    </header>

                    <section class="pedido-card__secao">
                        <h3 class="pedido-card__secao-titulo">Resumo</h3>
                        <dl class="pedido-dados">
                            <div>
                                <dt>Método de pagamento</dt>
                                <dd>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Não informado' }}</dd>
                            </div>
                            <div>
                                <dt>Valor total</dt>
                                <dd>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </section>
                    @if (!$pedido->endereco->cidade->nome)
                    <section class="pedido-card__secao">
                        <h3 class="pedido-card__secao-titulo">Endereço de entrega</h3>
                        <dl class="pedido-endereco">
                            <dd>retirado no local</dd>
                        </dl>
                    </section>
                    @else




                    <section class="pedido-card__secao">
                        <h3 class="pedido-card__secao-titulo">Endereço de entrega</h3>
                        <dl class="pedido-endereco">
                            <div>
                                <dt>Logradouro</dt>
                                <dd>{{ optional($pedido->endereco)->logradouro ?? 'Não informado' }}</dd>
                            </div>
                            <div>
                                <dt>Número</dt>
                                <dd>{{ optional($pedido->endereco)->numero ?? 's/n' }}</dd>
                            </div>
                            <div>
                                <dt>Bairro</dt>
                                <dd>{{ optional($pedido->endereco)->bairro ?? 'Não informado' }}</dd>
                            </div>
                            <div>
                                <dt>Complemento</dt>
                                <dd>{{ optional($pedido->endereco)->complemento ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>Cidade</dt>
                                <dd>{{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado' }}</dd>
                            </div>
                        </dl>
                    </section>

                    @endif
                    @if($pedido->itens->isNotEmpty())
                    <section class="pedido-card__secao">
                        <h3 class="pedido-card__secao-titulo">Itens do pedido</h3>
                        <ul class="pedido-itens">
                            @foreach($pedido->itens as $item)
                            <li class="pedido-itens__linha">
                                <div>
                                    <span class="pedido-itens__titulo">{{ optional($item->produto)->nome ?? 'Produto removido' }}</span>
                                    <span class="pedido-itens__detalhe">{{ $item->quantidade }}× R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
                                </div>
                                <strong>R$ {{ number_format((float) $item->quantidade * (float) $item->preco_unitario, 2, ',', '.') }}</strong>
                            </li>
                            @endforeach
                        </ul>
                    </section>
                    @endif
                </article>
                @endforeach
            </div>
            @endif


        </div>
    </main>
        </div>
    </div>
</body>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pedidos</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Pedido.css') }}">
</head>

<body class="ff-pedidos-page">
    <div class="ff-shell">
        @include('layouts.sidebar')

        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>

            <main>
                <div class="container mt-4 conteinner-pedidos">
                    <h1>Meus Pedidos</h1>

                    @if($pedidos->isEmpty())
                        <p class="text-muted">Voce nao possui pedidos.</p>
                    @else
                        @php
                            $statusFinalizados = [4, 5];
                            $pedidosEmAndamento = $pedidos->filter(fn ($pedido) => !in_array((int) $pedido->status, $statusFinalizados, true))->values();
                            $pedidosFinalizados = $pedidos->filter(fn ($pedido) => in_array((int) $pedido->status, $statusFinalizados, true))->values();
                        @endphp

                        <section class="pedidos-grupo pedidos-grupo--ativos">
                            <div class="pedidos-grupo__header">
                                <div>
                                    <span class="pedidos-grupo__etiqueta">Em percurso</span>
                                    <h2>Pedidos em andamento</h2>
                                </div>
                                <span class="pedidos-grupo__contador">{{ $pedidosEmAndamento->count() }}</span>
                            </div>

                            @if($pedidosEmAndamento->isEmpty())
                                <p class="pedidos-lista-vazia text-muted">Nenhum pedido em percurso agora.</p>
                            @else
                                <div class="lista-pedidos lista-pedidos--ativos">
                                    @foreach($pedidosEmAndamento as $pedido)
                                        @include('partials.pedido-cliente-card', ['pedido' => $pedido])
                                    @endforeach
                                </div>
                            @endif
                        </section>

                        @if($pedidosFinalizados->isNotEmpty())
                            <details class="pedidos-finalizados">
                                <summary class="pedidos-finalizados__summary">
                                    <span>Pedidos finalizados</span>
                                    <span class="pedidos-finalizados__contador">{{ $pedidosFinalizados->count() }}</span>
                                    <span class="pedidos-finalizados__icone" aria-hidden="true"></span>
                                </summary>
                                <div class="lista-pedidos lista-pedidos--finalizados">
                                    @foreach($pedidosFinalizados as $pedido)
                                        @include('partials.pedido-cliente-card', ['pedido' => $pedido])
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    @endif
                </div>
            </main>
        </div>
    </div>
</body>

</html>

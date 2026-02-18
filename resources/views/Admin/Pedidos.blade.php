<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de Pedidos</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Pedidos.css') }}">
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
                <span class="cabecalho-pedidos__etiqueta">Painel de pedidos</span>
                <h1 class="titulo-pagina">Olá, {{ $nomeUsuario ?? 'Administrador' }}!</h1>
                <p class="texto-suave mb-0">Acompanhe o avanço de cada pedido e atualize o status conforme o preparo.</p>
            </div>
            <div class="cabecalho-pedidos__dados">
                <span class="badge-total">{{ $totalPedidos }} pedidos ativos</span>
                <span class="badge-perfil">{{ $tipoUsuario ?? 'Equipe' }}</span>
            </div>
        </header>

        <section class="resumo-pedidos mb-4">
            @foreach($dashboardCards as $card)
                <article class="card-resumo {{ $card['accent'] }}">
                    <span class="card-resumo__rotulo">{{ $card['label'] }}</span>
                    <strong class="card-resumo__valor">{{ $card['valor'] }}</strong>
                </article>
            @endforeach
        </section>

        @if(session('sucesso'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('sucesso') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('erro'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('erro') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                <button class="acordeao-pedidos__gatilho" type="button" data-target="#pedidosFinalizados" aria-expanded="false">
                    <span>Pedidos finalizados</span>
                    <span class="acordeao-pedidos__contador">{{ count($pedidosPorStatus['finalizados'] ?? []) }}</span>
                    <span class="acordeao-pedidos__icone" aria-hidden="true"></span>
                </button>
                <div class="acordeao-pedidos__conteudo" id="pedidosFinalizados">
                    @forelse(($pedidosPorStatus['finalizados'] ?? collect()) as $pedido)
                        @include('Admin.partials.pedido-card', [
                            'pedido' => $pedido,
                            'statusOptions' => $statusOptions,
                            'statusTimeline' => $statusTimeline,
                            'statusLabels' => $statusLabels,
                            'desabilitarAcoes' => true,
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
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gatilhos = document.querySelectorAll('.acordeao-pedidos__gatilho');

            gatilhos.forEach((botao) => {
                const seletor = botao.dataset.target;
                const conteudo = document.querySelector(seletor);

                if (!conteudo) {
                    return;
                }

                botao.addEventListener('click', () => {
                    const expandido = botao.getAttribute('aria-expanded') === 'true';
                    botao.setAttribute('aria-expanded', String(!expandido));

                    if (expandido) {
                        conteudo.classList.remove('is-open');
                        conteudo.style.maxHeight = '0px';
                    } else {
                        conteudo.classList.add('is-open');
                        conteudo.style.maxHeight = `${conteudo.scrollHeight}px`;
                    }
                });

                // Ajusta altura caso já esteja marcado como aberto no HTML
                if (botao.getAttribute('aria-expanded') === 'true') {
                    conteudo.classList.add('is-open');
                    conteudo.style.maxHeight = `${conteudo.scrollHeight}px`;
                }
            });
        });
    </script>
        </div>
    </div>
</body>

</html>

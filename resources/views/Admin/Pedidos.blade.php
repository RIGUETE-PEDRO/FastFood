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
                <span class="badge-total" id="pedidos-total-badge">{{ $totalPedidos }} pedidos ativos</span>
                <span class="badge-perfil">{{ $tipoUsuario ?? 'Equipe' }}</span>
            </div>
        </header>

        <div id="pedidos-resumo-wrapper">
            @include('Admin.partials.pedidos-resumo-cards', ['dashboardCards' => $dashboardCards])
        </div>

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

        <div id="pedidos-lista-wrapper">
            @include('Admin.partials.pedidos-lista', [
                'pedidos' => $pedidos,
                'pedidosPorStatus' => $pedidosPorStatus,
                'statusOptions' => $statusOptions,
                'statusTimeline' => $statusTimeline,
                'statusLabels' => $statusLabels,
            ])
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pollingUrl = "{{ route('Pedidos.Poll') }}";
            let checksumAtual = "{{ $realtimeChecksum ?? '' }}";
            let atualizandoConteudo = false;
            const badgeTotal = document.getElementById('pedidos-total-badge');
            const resumoWrapper = document.getElementById('pedidos-resumo-wrapper');
            const listaWrapper = document.getElementById('pedidos-lista-wrapper');

            const inicializarInteracoes = () => {
                const gatilhos = document.querySelectorAll('.acordeao-pedidos__gatilho');

                gatilhos.forEach((botao) => {
                    const seletor = botao.dataset.target;
                    const conteudo = document.querySelector(seletor);

                    if (!conteudo) {
                        return;
                    }

                    const abrir = () => {
                        botao.setAttribute('aria-expanded', 'true');
                        conteudo.hidden = false;
                        conteudo.classList.add('is-open');
                    };

                    const fechar = () => {
                        botao.setAttribute('aria-expanded', 'false');
                        conteudo.classList.remove('is-open');
                        conteudo.hidden = true;
                    };

                    botao.addEventListener('click', () => {
                        const expandido = botao.getAttribute('aria-expanded') === 'true';
                        if (expandido) {
                            fechar();
                        } else {
                            abrir();
                        }
                    });

                    if (botao.getAttribute('aria-expanded') === 'true') {
                        abrir();
                    } else {
                        conteudo.hidden = true;
                    }
                });

                document.querySelectorAll('form[data-disable-on-submit]').forEach((form) => {
                    if (form.dataset.enhanced === '1') {
                        return;
                    }

                    form.dataset.enhanced = '1';
                    form.addEventListener('submit', () => {
                        const botao = form.querySelector('[data-avancar-button]');
                        if (!botao) {
                            return;
                        }

                        botao.classList.add('is-loading');
                        botao.setAttribute('disabled', 'disabled');
                    });
                });
            };

            const atualizarConteudo = async () => {
                const resposta = await fetch(`${pollingUrl}?full=1&t=${Date.now()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });

                if (!resposta.ok) {
                    return;
                }

                const dados = await resposta.json();
                if (!dados || !dados.checksum) {
                    return;
                }

                if (typeof dados.resumoHtml === 'string' && resumoWrapper) {
                    resumoWrapper.innerHTML = dados.resumoHtml;
                }

                if (typeof dados.listaHtml === 'string' && listaWrapper) {
                    listaWrapper.innerHTML = dados.listaHtml;
                }

                if (badgeTotal && dados.totalLabel) {
                    badgeTotal.textContent = dados.totalLabel;
                }

                checksumAtual = dados.checksum;
                inicializarInteracoes();
            };

            const verificarMudancas = async () => {
                if (atualizandoConteudo || document.visibilityState !== 'visible') {
                    return;
                }

                try {
                    const resposta = await fetch(`${pollingUrl}?t=${Date.now()}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });

                    if (!resposta.ok) {
                        return;
                    }

                    const dados = await resposta.json();
                    if (!dados || !dados.checksum) {
                        return;
                    }

                    if (checksumAtual && dados.checksum !== checksumAtual) {
                        atualizandoConteudo = true;
                        await atualizarConteudo();
                        atualizandoConteudo = false;
                    } else {
                        checksumAtual = dados.checksum;
                    }
                } catch (_) {
                    // Ignora falhas temporárias de rede para não poluir a UI.
                    atualizandoConteudo = false;
                }
            };

            inicializarInteracoes();
            window.setInterval(verificarMudancas, 8000);
        });
    </script>
        </div>
    </div>
</body>

</html>

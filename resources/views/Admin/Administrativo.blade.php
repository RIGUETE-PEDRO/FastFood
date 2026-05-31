<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>Administrativo</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">

</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>
            <main>
                <section class="dashboard-header">
                    <h1>Dashboard administrativo</h1>
                    <p>Ola, {{ $nomeUsuario ?? 'Usuario' }}. Acompanhe os indicadores do FlashFood.</p>
                </section>

                <section class="dashboard-toolbar painel">
                    <form method="GET" action="{{ route('Administrativo') }}" class="dashboard-filtro-form">
                        <label for="data_inicio">Data inicial</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="{{ $dataInicioSelecionada ?? now()->startOfMonth()->format('Y-m-d') }}">

                        <label for="data_fim">Data final</label>
                        <input type="date" id="data_fim" name="data_fim" value="{{ $dataFimSelecionada ?? now()->endOfMonth()->format('Y-m-d') }}">

                        <button type="submit">Aplicar</button>
                    </form>

                    <p class="dashboard-periodo">Periodo atual: <strong>{{ $periodoTexto ?? 'Mes atual' }}</strong></p>
                </section>

                <section class="dashboard-kpis">
                    <article class="painel painel-kpi">
                        <h2>Total de vendas</h2>
                        <strong>R$ {{ number_format((float) ($totalVendas ?? 0), 2, ',', '.') }}</strong>
                    </article>

                    <article class="painel painel-kpi">
                        <h2>Unidades vendidas</h2>
                        <strong>{{ (int) ($totalPedidos ?? 0) }}</strong>
                    </article>

                    <article class="painel painel-kpi">
                        <h2>Produto mais vendido</h2>
                        <strong>{{ $produtoMaisVendidoNome ?? 'Sem dados' }}</strong>
                        <span>{{ (int) ($produtoMaisVendidoQtd ?? 0) }} unidades</span>
                    </article>
                </section>

                <section class="dashboard-charts">
                    <article class="painel chart-card">
                        <h3>Pedidos por status</h3>
                        <canvas id="pedidosStatusChart" aria-label="Grafico de pedidos por status"></canvas>
                    </article>

                    <article class="painel chart-card">
                        <h3>Top 5 produtos vendidos</h3>
                        <canvas id="topProdutosChart" aria-label="Grafico de produtos mais vendidos"></canvas>
                    </article>
                </section>
            </main>
        </div>
    </div>

    <script id="dashboard-data" type="application/json">{!! json_encode([
        'statusLabels' => $statusLabels ?? [],
        'statusValores' => $statusValores ?? [],
        'topProdutosLabels' => $topProdutosLabels ?? [],
        'topProdutosValores' => $topProdutosValores ?? [],
    ], JSON_UNESCAPED_UNICODE) !!}</script>
    <script src="{{ asset('js/admin-dashboard-charts-fallback.js') }}?v={{ filemtime(public_path('js/admin-dashboard-charts-fallback.js')) }}"></script>

</body>

</html>

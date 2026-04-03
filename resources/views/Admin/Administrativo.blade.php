<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrativo</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                    <p>Olá, {{ $nomeUsuario ?? 'Usuário' }}. Acompanhe os indicadores do FlashFood.</p>
                </section>

                <section class="dashboard-toolbar painel">
                    <form method="GET" action="{{ route('Administrativo') }}" class="dashboard-filtro-form">
                        <label for="periodo">Agrupar por</label>
                        <select name="periodo" id="periodo" onchange="this.form.submit()">
                            <option value="dia" {{ ($periodoSelecionado ?? 'mes') === 'dia' ? 'selected' : '' }}>Dia</option>
                            <option value="mes" {{ ($periodoSelecionado ?? 'mes') === 'mes' ? 'selected' : '' }}>Mês</option>
                            <option value="ano" {{ ($periodoSelecionado ?? 'mes') === 'ano' ? 'selected' : '' }}>Ano</option>
                        </select>

                        @if (($periodoSelecionado ?? 'mes') === 'dia')
                            <input type="date" name="referencia" value="{{ $referenciaSelecionada ?? now()->format('Y-m-d') }}">
                        @elseif (($periodoSelecionado ?? 'mes') === 'ano')
                            <input type="number" min="2000" max="2100" step="1" name="referencia" value="{{ $referenciaSelecionada ?? now()->format('Y') }}">
                        @else
                            <input type="month" name="referencia" value="{{ $referenciaSelecionada ?? now()->format('Y-m') }}">
                        @endif

                        <button type="submit">Aplicar</button>
                    </form>

                    <p class="dashboard-periodo">Período atual: <strong>{{ $periodoTexto ?? 'Mês atual' }}</strong></p>
                </section>

                <section class="dashboard-kpis">
                    <article class="painel painel-kpi">
                        <h2>Total de vendas (entregues)</h2>
                        <strong>R$ {{ number_format((float) ($totalVendas ?? 0), 2, ',', '.') }}</strong>
                    </article>

                    <article class="painel painel-kpi">
                        <h2>Quantidade de pedidos</h2>
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
                        <canvas id="pedidosStatusChart" aria-label="Gráfico de pedidos por status"></canvas>
                    </article>

                    <article class="painel chart-card">
                        <h3>Top 5 produtos vendidos</h3>
                        <canvas id="topProdutosChart" aria-label="Gráfico de produtos mais vendidos"></canvas>
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

</body>

</html>

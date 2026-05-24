<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <title>Administrativo</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>
            <main>
                <section class="dashboard-header">
                    <h1>Dashboard administrativo</h1>
                    <p>Olá, <?php echo e($nomeUsuario ?? 'Usuário'); ?>. Acompanhe os indicadores do FlashFood.</p>
                </section>

                <section class="dashboard-toolbar painel">
                    <form method="GET" action="<?php echo e(route('Administrativo')); ?>" class="dashboard-filtro-form">
                        <label for="periodo">Agrupar por</label>
                        <select name="periodo" id="periodo" data-auto-submit-on-change>
                            <option value="dia" <?php echo e(($periodoSelecionado ?? 'mes') === 'dia' ? 'selected' : ''); ?>>Dia</option>
                            <option value="mes" <?php echo e(($periodoSelecionado ?? 'mes') === 'mes' ? 'selected' : ''); ?>>Mês</option>
                            <option value="ano" <?php echo e(($periodoSelecionado ?? 'mes') === 'ano' ? 'selected' : ''); ?>>Ano</option>
                        </select>

                        <?php if(($periodoSelecionado ?? 'mes') === 'dia'): ?>
                            <input type="date" name="referencia" value="<?php echo e($referenciaSelecionada ?? now()->format('Y-m-d')); ?>">
                        <?php elseif(($periodoSelecionado ?? 'mes') === 'ano'): ?>
                            <input type="number" min="2000" max="2100" step="1" name="referencia" value="<?php echo e($referenciaSelecionada ?? now()->format('Y')); ?>">
                        <?php else: ?>
                            <input type="month" name="referencia" value="<?php echo e($referenciaSelecionada ?? now()->translatedFormat('F')); ?>">
                        <?php endif; ?>

                        <button type="submit">Aplicar</button>
                    </form>

                    <p class="dashboard-periodo">Período atual: <strong><?php echo e($periodoTexto ?? 'Mês atual'); ?></strong></p>
                </section>

                <section class="dashboard-kpis">
                    <article class="painel painel-kpi">
                        <h2>Total de vendas</h2>
                        <strong>R$ <?php echo e(number_format((float) ($totalVendas ?? 0), 2, ',', '.')); ?></strong>
                    </article>

                    <article class="painel painel-kpi">
                        <h2>Unidades vendidas</h2>
                        <strong><?php echo e((int) ($totalPedidos ?? 0)); ?></strong>
                    </article>

                    <article class="painel painel-kpi">
                        <h2>Produto mais vendido</h2>
                        <strong><?php echo e($produtoMaisVendidoNome ?? 'Sem dados'); ?></strong>
                        <span><?php echo e((int) ($produtoMaisVendidoQtd ?? 0)); ?> unidades</span>
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

    <script id="dashboard-data" type="application/json"><?php echo json_encode([
        'statusLabels' => $statusLabels ?? [],
        'statusValores' => $statusValores ?? [],
        'topProdutosLabels' => $topProdutosLabels ?? [],
        'topProdutosValores' => $topProdutosValores ?? [],
    ], JSON_UNESCAPED_UNICODE); ?></script>

</body>

</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/Administrativo.blade.php ENDPATH**/ ?>
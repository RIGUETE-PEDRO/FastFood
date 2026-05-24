<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Painel de Pedidos</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Pedidos.css')); ?>">
</head>

<body>
    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>

    <main
        class="container py-4 pedidos-admin"
        id="pedidos-admin-root"
        data-polling-url="<?php echo e(route('Pedidos.Poll')); ?>"
        data-checksum="<?php echo e($realtimeChecksum ?? ''); ?>"
    >
        <header class="cabecalho-pedidos mb-4">
            <div class="cabecalho-pedidos__titulo">
                <span class="cabecalho-pedidos__etiqueta">Painel de pedidos</span>
                <h1 class="titulo-pagina">Olá, <?php echo e($nomeUsuario ?? 'Administrador'); ?>!</h1>
                <p class="texto-suave mb-0">Acompanhe o avanço de cada pedido e atualize o status conforme o preparo.</p>
            </div>
            <div class="cabecalho-pedidos__dados">
                <span class="badge-total" id="pedidos-total-badge"><?php echo e($totalPedidos); ?> pedidos ativos</span>
                <span class="badge-perfil"><?php echo e($tipoUsuario ?? 'Equipe'); ?></span>
            </div>
        </header>

        <div id="pedidos-resumo-wrapper">
            <?php echo $__env->make('Admin.partials.pedidos-resumo-cards', ['dashboardCards' => $dashboardCards], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>



        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div id="pedidos-lista-wrapper">
            <?php echo $__env->make('Admin.partials.pedidos-lista', [
                'pedidos' => $pedidos,
                'pedidosPorStatus' => $pedidosPorStatus,
                'statusOptions' => $statusOptions,
                'statusTimeline' => $statusTimeline,
                'statusLabels' => $statusLabels,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </main>
        </div>
    </div>
    <?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/Pedidos.blade.php ENDPATH**/ ?>
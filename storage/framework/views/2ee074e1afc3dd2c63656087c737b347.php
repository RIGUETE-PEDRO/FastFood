<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Pedidos</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Index.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Pedido.css')); ?>">
</head>

<body class="ff-pedidos-page">
    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>

            <main>
                <div class="container mt-4 conteinner-pedidos">
                    <h1>Meus Pedidos</h1>

                    <?php if($pedidos->isEmpty()): ?>
                        <p class="text-muted">Voce nao possui pedidos.</p>
                    <?php else: ?>
                        <div class="lista-pedidos mt-3">
                            <?php $__currentLoopData = $pedidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $statusTexto = strtoupper((string) optional($pedido->statusRelacionamento)->status);
                                    $statusClasse = match ($statusTexto) {
                                        'PENDENTE' => 'badge-status badge-status--pendente',
                                        'EM PREPARO', 'PREPARANDO' => 'badge-status badge-status--preparo',
                                        'ENTREGUE' => 'badge-status badge-status--entregue',
                                        'CANCELADO' => 'badge-status badge-status--cancelado',
                                        default => 'badge-status badge-status--padrao',
                                    };
                                    $temEnderecoEntrega = filled(optional($pedido->endereco)->logradouro);
                                ?>

                                <article class="pedido-card">
                                    <header class="pedido-card__header">
                                        <div>
                                            <h2 class="pedido-card__titulo">Pedido #<?php echo e($pedido->id); ?></h2>
                                            <span class="pedido-card__subtitulo">Realizado em <?php echo e(optional($pedido->created_at)->format('d/m/Y \a\s H:i') ?? 'N/D'); ?></span>
                                        </div>
                                        <span class="<?php echo e($statusClasse); ?>"><?php echo e($statusTexto !== '' ? $statusTexto : 'STATUS INDEFINIDO'); ?></span>
                                    </header>

                                    <section class="pedido-card__secao">
                                        <h3 class="pedido-card__secao-titulo">Resumo</h3>
                                        <dl class="pedido-dados">
                                            <div>
                                                <dt>Metodo de pagamento</dt>
                                                <dd><?php echo e(optional($pedido->formaPagamento)->tipo_pagamento ?? 'Nao informado'); ?></dd>
                                            </div>
                                            <div>
                                                <dt>Valor total</dt>
                                                <dd>R$ <?php echo e(number_format((float) $pedido->valor_total, 2, ',', '.')); ?></dd>
                                            </div>
                                            <div>
                                                <dt>Tipo do pedido</dt>
                                                <dd><?php echo e($temEnderecoEntrega ? 'Entrega' : 'Retirada no local'); ?></dd>
                                            </div>
                                        </dl>
                                    </section>

                                    <?php if($temEnderecoEntrega): ?>
                                        <section class="pedido-card__secao">
                                            <h3 class="pedido-card__secao-titulo">Endereco de entrega</h3>
                                            <dl class="pedido-endereco">
                                                <div>
                                                    <dt>Logradouro</dt>
                                                    <dd><?php echo e(optional($pedido->endereco)->logradouro ?? 'Nao informado'); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Numero</dt>
                                                    <dd><?php echo e(optional($pedido->endereco)->numero ?? 's/n'); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Bairro</dt>
                                                    <dd><?php echo e(optional($pedido->endereco)->bairro ?? 'Nao informado'); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Complemento</dt>
                                                    <dd><?php echo e(optional($pedido->endereco)->complemento ?? '-'); ?></dd>
                                                </div>
                                                <div>
                                                    <dt>Cidade</dt>
                                                    <dd><?php echo e(optional(optional($pedido->endereco)->cidade)->nome ?? 'Nao informado'); ?></dd>
                                                </div>
                                            </dl>
                                        </section>
                                    <?php endif; ?>

                                    <?php if($pedido->itens->isNotEmpty()): ?>
                                        <section class="pedido-card__secao">
                                            <h3 class="pedido-card__secao-titulo">Itens do pedido</h3>
                                            <ul class="pedido-itens">
                                                <?php $__currentLoopData = $pedido->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="pedido-itens__linha">
                                                        <div>
                                                            <span class="pedido-itens__titulo"><?php echo e(optional($item->produto)->nome ?? 'Produto removido'); ?></span>
                                                            <span class="pedido-itens__detalhe"><?php echo e($item->quantidade); ?> x R$ <?php echo e(number_format((float) $item->preco_unitario, 2, ',', '.')); ?></span>
                                                        </div>
                                                        <strong>R$ <?php echo e(number_format((float) $item->quantidade * (float) $item->preco_unitario, 2, ',', '.')); ?></strong>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </section>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Pedido.blade.php ENDPATH**/ ?>
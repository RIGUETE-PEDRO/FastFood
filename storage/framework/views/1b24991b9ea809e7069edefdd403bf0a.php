<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <title>Painel de Entregas</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Pedidos.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Entregas.css')); ?>">
</head>

<body>
    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>


            <main class="container py-4 pedidos-admin">
                <header class="cabecalho-pedidos mb-4">
                    <div class="cabecalho-pedidos__titulo">
                        <span class="cabecalho-pedidos__etiqueta">Entregas</span>
                        <h1 class="titulo-pagina">Olá, <?php echo e($nomeUsuario ?? 'Entregador'); ?>!</h1>
                        <p class="texto-suave mb-0">Veja os pedidos com nome do cliente e endereço para entrega.</p>
                    </div>
                    <div class="cabecalho-pedidos__dados">
                        <span class="badge-total"><?php echo e($totalPedidosEntrega ?? 0); ?> pedidos de entrega</span>
                    </div>
                </header>

                <section class="lista-pedidos-admin">
                    <h2 class="secao-titulo secao-titulo--disponiveis">Pedidos disponíveis para atribuir</h2>

                    <?php $__empty_1 = true; $__currentLoopData = ($pedidosAbertos ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                    $statusValor = (int) ($pedido->status_enum->value ?? $pedido->status ?? 0);
                    $statusClasse = match ($statusValor) {
                    1 => 'badge-status badge-status--pendente',
                    2 => 'badge-status badge-status--preparo',
                    3 => 'badge-status badge-status--padrao',
                    4 => 'badge-status badge-status--entregue',
                    5 => 'badge-status badge-status--cancelado',
                    default => 'badge-status badge-status--padrao',
                    };
                    ?>

                    <article class="pedido-card entregas-card" data-status="<?php echo e($statusValor); ?>">
                        <header class="pedido-card__header">
                            <div>
                                <h3 class="pedido-card__titulo">Pedido #<?php echo e($pedido->id); ?></h3>
                                <span class="pedido-card__subtitulo"><?php echo e(optional($pedido->created_at)->format('d/m/Y \à\s H:i') ?? 'N/D'); ?></span>
                            </div>
                            <span class="<?php echo e($statusClasse); ?>"><?php echo e(strtoupper((string) ($pedido->status_label ?? ''))); ?></span>
                        </header>

                        <section class="pedido-card__secao">
                            <h4 class="pedido-card__secao-titulo">Cliente</h4>
                            <p class="mb-1"><strong>Nome:</strong> <?php echo e(optional($pedido->usuario)->nome ?? 'Não informado'); ?></p>
                            <p class="mb-0"><strong>Telefone:</strong> <?php echo e(optional($pedido->usuario)->telefone ?? 'Não informado'); ?></p>
                        </section>

                        <section class="pedido-card__secao">
                            <h4 class="pedido-card__secao-titulo">Endereço de entrega</h4>
                            <p class="mb-1"><strong>Logradouro:</strong> <?php echo e(optional($pedido->endereco)->logradouro ?? 'Não informado'); ?></p>
                            <p class="mb-1"><strong>Número:</strong> <?php echo e(optional($pedido->endereco)->numero ?? 's/n'); ?></p>
                            <p class="mb-1"><strong>Bairro:</strong> <?php echo e(optional($pedido->endereco)->bairro ?? 'Não informado'); ?></p>
                            <p class="mb-1"><strong>Complemento:</strong> <?php echo e(optional($pedido->endereco)->complemento ?: '—'); ?></p>
                            <p class="mb-0"><strong>Cidade:</strong> <?php echo e(optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado'); ?></p>
                        </section>

                        <section class="pedido-card__secao">
                            <h4 class="pedido-card__secao-titulo">Motoboy</h4>
                            <?php
                            $motoboyAtualId = (int) (optional($usuario)->id ?? 0);
                            $motoboyPedidoId = (int) (optional($pedido)->motoboy_id ?? 0);
                            $statusPedido = (int) (optional($pedido)->status ?? 0);
                            $etapaEntrega = $statusPedido === 3;
                            ?>

                            <?php if($motoboyPedidoId === 0 && $etapaEntrega): ?>
                            <form action="<?php echo e(route('entregas.aceitar', $pedido->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-add">Aceitar entrega</button>
                            </form>
                            <?php elseif($motoboyPedidoId === 0 && !$etapaEntrega): ?>
                            <p class="mb-0"><strong>Apenas visualização.</strong> Aguarde o pedido entrar na etapa de entrega.</p>
                            <?php elseif($motoboyPedidoId === $motoboyAtualId): ?>
                            <p class="mb-2"><strong>Vinculado a você.</strong></p>
                            <?php if($etapaEntrega): ?>
                            <form action="<?php echo e(route('entregas.finalizar', $pedido->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-add">Finalizar entrega</button>
                            </form>
                            <?php else: ?>
                            <p class="mb-0">Aguardando status de entrega para finalizar.</p>
                            <?php endif; ?>
                            <?php else: ?>
                            <p class="mb-0">
                                <strong>Já vinculado:</strong>
                                <?php echo e(optional($pedido->motoboy)->nome ?? 'Outro motoboy'); ?>

                            </p>
                            <?php endif; ?>
                        </section>
                    </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-4">
                            <p class="texto-suave mb-0">Nenhum pedido de entrega em aberto no momento.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <section class="lista-pedidos-admin mt-4">
                    <h2 class="secao-titulo secao-titulo--aceitos">Pedidos aceitos</h2>

                    <?php $__empty_1 = true; $__currentLoopData = ($pedidosAceitos ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article class="pedido-card entregas-card" data-status="<?php echo e((int) ($pedido->status ?? 0)); ?>">
                        <header class="pedido-card__header">
                            <div>
                                <h3 class="pedido-card__titulo">Pedido #<?php echo e($pedido->id); ?></h3>
                                <span class="pedido-card__subtitulo">
                                    Cliente: <?php echo e(optional($pedido->usuario)->nome ?? 'Não informado'); ?>

                                </span>
                            </div>
                            <span class="badge-status badge-status--padrao">
                                <?php echo e(strtoupper((string) ($pedido->status_label ?? ''))); ?>

                            </span>
                        </header>
                        <p class="mb-0">
                            <strong>Motoboy:</strong>
                            <?php echo e(optional($pedido->motoboy)->nome ?? 'Não vinculado'); ?>

                        </p>
                        <p class="mb-0">
                            <strong>Endereço:</strong>
                            <?php echo e(optional($pedido->endereco)->logradouro ?? 'Não informado'); ?>,
                            <?php echo e(optional($pedido->endereco)->numero ?? 's/n'); ?>,
                            <?php echo e(optional($pedido->endereco)->bairro ?? 'Não informado'); ?>,
                            <?php echo e(optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado'); ?>

                        </p>



                        <?php
                        $motoboyAtualId = (int) (optional($usuario)->id ?? 0);
                        $motoboyPedidoId = (int) (optional($pedido)->motoboy_id ?? 0);
                        $statusPedido = (int) (optional($pedido)->status ?? 0);
                        $etapaEntrega = $statusPedido === 3;
                        ?>

                        <?php if($motoboyPedidoId === $motoboyAtualId && $etapaEntrega): ?>
                        <div class="mt-3">
                            <form action="<?php echo e(route('entregas.finalizar', $pedido->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn-add">Finalizar entrega</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-4">
                            <p class="texto-suave mb-0">Nenhum pedido aceito até o momento.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <section class="acordeao-pedidos">
                    <button class="acordeao-pedidos__gatilho" type="button" data-target="#entregasFinalizadas" aria-controls="entregasFinalizadas" aria-expanded="false">
                        <span>Entregas finalizadas</span>
                        <span class="acordeao-pedidos__contador"><?php echo e(count($pedidosFinalizados ?? [])); ?></span>
                        <span class="acordeao-pedidos__icone" aria-hidden="true"></span>
                    </button>
                    <div class="acordeao-pedidos__conteudo" id="entregasFinalizadas" hidden>
                        <?php $__empty_1 = true; $__currentLoopData = ($pedidosFinalizados ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="pedido-card" data-status="<?php echo e((int) ($pedido->status ?? 0)); ?>">
                            <header class="pedido-card__header">
                                <div>
                                    <h3 class="pedido-card__titulo">Pedido #<?php echo e($pedido->id); ?></h3>
                                    <span class="pedido-card__subtitulo"><strong>Cliente:</strong> <?php echo e(optional($pedido->usuario)->nome ?? 'Não informado'); ?></span><br>
                                    <span class="pedido-card__subtitulo"><strong>Endereço:</strong> <?php echo e(optional($pedido->endereco)->logradouro ?? 'Não informado'); ?>, <?php echo e(optional($pedido->endereco)->numero ?? 's/n'); ?>, <?php echo e(optional($pedido->endereco)->bairro ?? 'Não informado'); ?>, <?php echo e(optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado'); ?></span><br>
                                    <span class="pedido-card__subtitulo"><strong>Motoboy:</strong> <?php echo e(optional($pedido->motoboy)->nome ?? 'Não vinculado'); ?></span>
                                    <span class="pedido-card__subtitulo"><strong>Finalizado:</strong> em: <?php echo e(optional($pedido->updated_at)->format('d/m/Y \à\s H:i') ?? 'N/D'); ?></span>
                                </div>
                                <span class="badge-status badge-status--entregue"><?php echo e(strtoupper((string) ($pedido->status_label ?? 'FINALIZADO'))); ?></span>
                            </header>
                        </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center py-4">
                                <p class="texto-suave mb-0">Nenhuma entrega finalizada ainda.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/Entregas.blade.php ENDPATH**/ ?>
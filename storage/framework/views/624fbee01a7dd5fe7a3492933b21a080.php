<?php if($pedidos->isEmpty()): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="titulo-card">Nenhum pedido no momento</h2>
            <p class="texto-suave mb-0">Assim que os clientes realizarem pedidos eles aparecerão aqui.</p>
        </div>
    </div>
<?php else: ?>
    <section class="lista-pedidos-admin">
        <h2 class="secao-titulo">Pedidos em andamento</h2>
        <?php $__empty_1 = true; $__currentLoopData = ($pedidosPorStatus['abertos'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php echo $__env->make('Admin.partials.pedido-card', [
                'pedido' => $pedido,
                'statusOptions' => $statusOptions,
                'statusTimeline' => $statusTimeline,
                'statusLabels' => $statusLabels,
                'desabilitarAcoes' => false,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <p class="texto-suave mb-0">Nenhum pedido em preparo ou a caminho agora.</p>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <section class="acordeao-pedidos">
        <button class="acordeao-pedidos__gatilho" type="button" data-target="#pedidosFinalizados" aria-controls="pedidosFinalizados" aria-expanded="false">
            <span>Pedidos finalizados</span>
            <span class="acordeao-pedidos__contador"><?php echo e(count($pedidosPorStatus['finalizados'] ?? [])); ?></span>
            <span class="acordeao-pedidos__icone" aria-hidden="true"></span>
        </button>
        <div class="acordeao-pedidos__conteudo" id="pedidosFinalizados" hidden>
            <?php $__empty_1 = true; $__currentLoopData = ($pedidosPorStatus['finalizados'] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('Admin.partials.pedido-card', [
                    'pedido' => $pedido,
                    'statusOptions' => $statusOptions,
                    'statusTimeline' => $statusTimeline,
                    'statusLabels' => $statusLabels,
                    'desabilitarAcoes' => true,
                    'colapsavel' => true,
                    'iniciarRecolhido' => true,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-4">
                        <p class="texto-suave mb-0">Nenhum pedido entregue ou cancelado ainda.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/partials/pedidos-lista.blade.php ENDPATH**/ ?>
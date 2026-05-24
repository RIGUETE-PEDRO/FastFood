<?php
    $statusAtual = $pedido->status_enum;
    $nextStatus = $pedido->next_status;
    $statusAtualValor = $statusAtual->value ?? 0;
    $colapsavel = (bool) ($colapsavel ?? false);
    $iniciarRecolhido = (bool) ($iniciarRecolhido ?? false);
    $statusClasses = [
        1 => 'badge-status badge-status--pendente',
        2 => 'badge-status badge-status--preparo',
        3 => 'badge-status badge-status--expedicao',
        4 => 'badge-status badge-status--entregue',
        5 => 'badge-status badge-status--cancelado',
    ];
    $classeStatus = $statusClasses[$statusAtualValor] ?? 'badge-status badge-status--padrao';
    $temEnderecoEntrega = filled(optional($pedido->endereco)->logradouro);
?>

<article class="pedido-card shadow-sm" data-status="<?php echo e($statusAtualValor); ?>">
    <?php if($colapsavel): ?>
        <details class="pedido-collapse" <?php if(!$iniciarRecolhido): ?> open <?php endif; ?>>
            <summary class="pedido-collapse__summary">
                <header class="pedido-card__header">
                    <div>
                        <h2 class="pedido-card__titulo">Pedido #<?php echo e($pedido->id); ?></h2>
                        <p class="pedido-card__subtitulo mb-0"><?php echo e(optional($pedido->created_at)->format('d/m/Y \a\s H:i') ?? 'Data nao informada'); ?></p>
                        <p class="pedido-card__cliente mb-0">Cliente: <?php echo e(optional($pedido->usuario)->nome ?? 'Desconhecido'); ?></p>
                    </div>
                    <div class="pedido-card__header-right">
                        <span class="<?php echo e($classeStatus); ?>"><?php echo e($pedido->status_label); ?></span>
                        <span class="pedido-collapse__chevron" aria-hidden="true">v</span>
                    </div>
                </header>
            </summary>

            <div class="pedido-collapse__content">
    <?php else: ?>
        <header class="pedido-card__header">
            <div>
                <h2 class="pedido-card__titulo">Pedido #<?php echo e($pedido->id); ?></h2>
                <p class="pedido-card__subtitulo mb-0"><?php echo e(optional($pedido->created_at)->format('d/m/Y \a\s H:i') ?? 'Data nao informada'); ?></p>
                <p class="pedido-card__cliente mb-0">Cliente: <?php echo e(optional($pedido->usuario)->nome ?? 'Desconhecido'); ?></p>
            </div>
            <div class="pedido-card__header-right">
                <span class="<?php echo e($classeStatus); ?>"><?php echo e($pedido->status_label); ?></span>
            </div>
        </header>

        <div class="pedido-card__body">
    <?php endif; ?>
        <section class="pedido-card__secao">
            <h3 class="pedido-card__secao-titulo">Linha do tempo</h3>
            <ol class="timeline-status">
                <?php $__currentLoopData = $statusTimeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $ativo = $statusItem['value'] === $statusAtualValor;
                        $concluido = $statusItem['value'] < $statusAtualValor;
                    ?>
                    <li class="timeline-status__item <?php echo e($ativo ? 'is-ativo' : ''); ?> <?php echo e($concluido ? 'is-concluido' : ''); ?>">
                        <span class="timeline-status__etapa"><?php echo e($statusItem['label']); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        </section>

        <section class="pedido-card__secao">
            <h3 class="pedido-card__secao-titulo">Resumo</h3>
            <dl class="pedido-dados">
                <div>
                    <dt>Pagamento</dt>
                    <dd><?php echo e(optional($pedido->formaPagamento)->tipo_pagamento ?? 'Nao informado'); ?></dd>
                </div>
                <div>
                    <dt>Total</dt>
                    <dd>R$ <?php echo e(number_format((float) $pedido->valor_total, 2, ',', '.')); ?></dd>
                </div>
                <div>
                    <dt><?php echo e($temEnderecoEntrega ? 'Endereco' : 'Atendimento'); ?></dt>
                    <dd>
                        <?php if($temEnderecoEntrega): ?>
                            <?php echo e($pedido->endereco->logradouro); ?>, <?php echo e($pedido->endereco->numero ?? 's/n'); ?> - <?php echo e($pedido->endereco->bairro ?? ''); ?><br>
                            <?php echo e(optional(optional($pedido->endereco)->cidade)->nome ?? ''); ?>

                        <?php else: ?>
                            Retirada no local
                            <?php if(optional($pedido->mesa)->numero_da_mesa): ?>
                                <br>Mesa <?php echo e($pedido->mesa->numero_da_mesa); ?>

                            <?php endif; ?>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
        </section>

        <?php if($pedido->itens->isNotEmpty()): ?>
            <section class="pedido-card__secao">
                <h3 class="pedido-card__secao-titulo">Itens</h3>
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

        <?php if (! (!empty($desabilitarAcoes))): ?>
            <section class="pedido-card__secao pedido-card__secao--acoes">
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3">
                    <form class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-3" method="POST" action="<?php echo e(route('Pedidos.StatusAtualizar', $pedido)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <label class="form-label mb-0 me-sm-2" for="status-<?php echo e($pedido->id); ?>">Atualizar status</label>
                        <select id="status-<?php echo e($pedido->id); ?>" name="status" class="form-select">
                            <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($option['value']); ?>" <?php if($option['value'] === $statusAtualValor): echo 'selected'; endif; ?>>
                                    <?php echo e($option['label']); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </form>

                    <?php if($nextStatus): ?>
                        <form class="pedido-avancar-form" method="POST" action="<?php echo e(route('Pedidos.StatusAvancar', $pedido)); ?>" data-disable-on-submit>
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-success btn-avancar-status" data-avancar-button aria-label="Avancar status do pedido #<?php echo e($pedido->id); ?>">
                                <span class="btn-avancar-status__text">
                                    Avancar para <?php echo e($statusLabels[$nextStatus->value] ?? 'proximo status'); ?>

                                </span>
                                <span class="btn-avancar-status__loading" aria-hidden="true">Avancando...</span>
                                <span class="btn-avancar-status__icon" aria-hidden="true">-&gt;</span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="GET" action="<?php echo e(route('Pedidos.GerarCupom', $pedido)); ?>" class="m-0">
                        <button type="submit" class="btn btn-outline-secondary btn-geraCupom" aria-label="Gerar cupom do pedido #<?php echo e($pedido->id); ?>">Gerar cupom</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    <?php if($colapsavel): ?>
            </div>
        </details>
    <?php else: ?>
        </div>
    <?php endif; ?>
</article>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/partials/pedido-card.blade.php ENDPATH**/ ?>
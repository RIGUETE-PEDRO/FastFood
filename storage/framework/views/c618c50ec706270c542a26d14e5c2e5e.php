<section class="resumo-pedidos mb-4">
    <?php $__currentLoopData = $dashboardCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="card-resumo <?php echo e($card['accent']); ?>">
            <span class="card-resumo__rotulo"><?php echo e($card['label']); ?></span>
            <strong class="card-resumo__valor"><?php echo e($card['valor']); ?></strong>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/partials/pedidos-resumo-cards.blade.php ENDPATH**/ ?>
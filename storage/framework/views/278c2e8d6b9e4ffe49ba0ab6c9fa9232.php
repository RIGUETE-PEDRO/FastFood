<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Home</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>?v=<?php echo e(filemtime(public_path('css/Admin/Principal.css'))); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('css/Index.css')); ?>?v=<?php echo e(filemtime(public_path('css/Index.css'))); ?>">
</head>

<body>

    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>
    <main>

        <!-- CARROSSEL -->
        <div class="carousel-produtos">
            <div class="carousel-track">
                <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="produto-card-mini" data-produto-id="<?php echo e($produto->id); ?>" data-produto-nome="<?php echo e($produto->nome); ?>" data-produto-preco="<?php echo e($produto->preco); ?>">
                    <div class="mini-img">
                        <img src="<?php echo e(asset('img/produtos/' . $produto->imagem_url)); ?>" alt="<?php echo e($produto->nome); ?>">
                    </div>

                    <div class="mini-info">
                        <span class="mini-titulo"><?php echo e($produto->nome); ?></span>
                        <span class="mini-preco">
                    R$ <?php echo e(number_format((float) $produto->preco, 2, ',', '.')); ?>

                </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="produto-card-mini" data-produto-id="<?php echo e($produto->id); ?>" data-produto-nome="<?php echo e($produto->nome); ?>" data-produto-preco="<?php echo e($produto->preco); ?>">
                    <div class="mini-img">
                        <img src="<?php echo e(asset('img/produtos/' . $produto->imagem_url)); ?>" alt="<?php echo e($produto->nome); ?>">
                    </div>

                    <div class="mini-info">
                        <span class="mini-titulo"><?php echo e($produto->nome); ?></span>
                        <span class="mini-preco">
                    R$ <?php echo e(number_format((float) $produto->preco, 2, ',', '.')); ?>

                </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>
        </div>




        <div class="container-produtos mt-4">
            <?php $__currentLoopData = $produtos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="produto produto--interactive" data-produto-id="<?php echo e($produto->id); ?>" data-produto-nome="<?php echo e($produto->nome); ?>" data-produto-preco="<?php echo e($produto->preco); ?>">
                <div class="container-img">
                    <img src="<?php echo e(asset('img/produtos/' . $produto->imagem_url)); ?>" alt="<?php echo e($produto->nome); ?>" loading="lazy">
                    <span class="produto-badge" aria-label="Preço">R$ <?php echo e(number_format((float) $produto->preco, 2, ',', '.')); ?></span>
                </div>
                <div class="preco-conteiner">
                    <label class="lanche"><?php echo e($produto->nome); ?></label>
                </div>
                <?php if(!empty($produto->descricao)): ?>
                <div class="ingredientes-wrap">
                    <span class="ingredientes">ingredientes:</span>
                    <div class="ingredientes-lista"><?php echo e($produto->descricao); ?></div>
                </div>
                <?php else: ?>
                <br>
                <br>
                <?php endif; ?>
                <div>
                    <button type="button" class="button-adicionar">Adicionar ao carrinho</button>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Modal: Adicionar ao carrinho -->
        <div class="modal fade " id="addToCartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content conteiner-info">
                    <form method="POST" action="<?php echo e(route('carrinho.adicionar')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h5 class="modal-title text_modal">Adicionar ao carrinho</h5>

                            <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" id="cart_produto_id" name="produto_id" value="">
                            <input type="hidden" id="cart_preco" name="preco" value="">

                            <div class="mb-3">
                                <label class="form-label text_modal">Produto</label>
                                <input type="text" class="form-control" id="cart_produto_nome" value="" readonly disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text_modal" for="cart_quantidade">Quantidade</label>
                                <input type="number" class="form-control" id="cart_quantidade" name="quantidade" min="1" value="1" required>
                            </div>

                            <div class="mb-0 ">
                                <label class="form-label text_modal" for="cart_observacao">Observação (opcional)</label>
                                <textarea class="form-control" id="cart_observacao" name="observacao" rows="3" placeholder="Ex: sem cebola, bem passado..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancelar " data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-confirmar">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
        </div>
    </div>
<?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script src="<?php echo e(asset('js/carousel.js')); ?>"></script>
</body>

</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Index.blade.php ENDPATH**/ ?>
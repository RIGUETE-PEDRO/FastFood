<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo | Admin</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/BemVindo.css')); ?>">
</head>

<body>
    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>

            <main class="bemvindo">
                <section class="bemvindo-hero painel">
                    <div>
                        <span class="bemvindo-hero__tag">Painel administrativo</span>
                        <h1>Bem-vindo, <?php echo e($nomeUsuario ?? 'Usuário'); ?>!</h1>
                        <p class="bemvindo-hero__subtitle">
                            Acesse rapidamente as áreas principais e acompanhe o fluxo do seu restaurante.
                        </p>
                    </div>
                    <div class="bemvindo-hero__badge">
                        <span>Perfil</span>
                        <strong><?php echo e($tipoUsuario ?? 'Usuário'); ?></strong>
                    </div>
                </section>

                <section class="bemvindo-cards">
                    <article class="bemvindo-card painel">
                        <h2>Dicas rápidas</h2>
                        <ul class="bemvindo-tips">
                            <li>Revise os pedidos pendentes para evitar atrasos.</li>
                            <li>Mantenha o cardápio atualizado antes do horário de pico.</li>
                            <li>Confira as entregas em andamento e reatribua quando necessário.</li>
                        </ul>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/BemVindo.blade.php ENDPATH**/ ?>
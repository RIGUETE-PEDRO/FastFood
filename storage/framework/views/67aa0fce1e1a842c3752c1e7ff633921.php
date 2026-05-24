<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <title>Configurações | Admin</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Principal.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/Admin/Configuracoes.css')); ?>">
</head>

<body>
    <div class="ff-shell">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>

            <main class="config-admin">
                <section class="config-hero painel">
                    <div>
                        <span class="config-hero__tag">Configurações</span>
                        <h1>Preferências do sistema</h1>
                        <p>Controle recursos administrativos usados no atendimento.</p>
                    </div>
                    <div class="config-hero__badge">
                        <span>Acesso</span>
                        <strong><?php echo e($tipoUsuario ?? 'Administrador'); ?></strong>
                    </div>
                </section>

                <section class="config-grid">
                    <article class="config-card painel">
                        <div>
                            <h2>Notificações de pedidos</h2>
                            <p>Receba um aviso do navegador quando chegar pedido pendente para aceitar.</p>
                        </div>

                        <div class="config-card__actions">
                            <button type="button" class="btn-config-notification" id="pedidos-enable-notifications">
                                Ativar notificações
                            </button>
                            <span class="config-card__hint" id="notification-settings-status">
                                As notificações dependem da permissão do navegador.
                            </span>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/Admin/Configuracoes.blade.php ENDPATH**/ ?>
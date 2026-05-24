<?php
$isAdmin = request()->routeIs('Administrativo')
|| request()->routeIs('admin.bemvindo')
|| request()->routeIs('gerenciamento_*')
|| request()->routeIs('Pedidos_Administrativo')
|| request()->routeIs('gerenciamento_Produtos')
|| request()->routeIs('Cadastrar_Produto')
|| request()->routeIs('deletar_produto')
|| request()->routeIs('mesas.*')
|| request()->routeIs('garcom')
|| request()->routeIs('entregas');
?>

<nav class="ff-sidebar d-flex flex-column">
    <div class="ff-sidebar__brand d-flex align-items-center justify-content-between">
        <span class="ff-sidebar__logo">FlashFood</span>
        <span class="ff-sidebar__Versao">V 1.0</span>
        <button type="button" class="ff-sidebar__close" data-sidebar-toggle aria-label="Fechar menu">
            ✕
        </button>
    </div>

    <?php if($isAdmin): ?>
    <ul class="nav nav-pills flex-column mb-auto ff-sidebar__nav">
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'DASHBORD')): ?>
        <li class="nav-item">
            <a href="<?php echo e(route('Administrativo', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('Administrativo') ? 'active' : ''); ?>">DashBoard</a>
        </li>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'GERENCIAMENTO_FUNCIONARIOS')): ?>
        <li class="nav-item">
            <a href="<?php echo e(route('gerenciamento_funcionarios', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('gerenciamento_funcionarios') ? 'active' : ''); ?>">Gerenciamento de Funcionários</a>
        </li>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'PEDIDOS')): ?>
        <li class="nav-item">
            <a href="<?php echo e(route('Pedidos_Administrativo', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('Pedidos_Administrativo') ? 'active' : ''); ?>">Pedidos</a>
        </li>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'GERENCIAMENTO_PRODUTOS')): ?>
        <li class="nav-item">
            <a href="<?php echo e(route('gerenciamento_produtos', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('gerenciamento_produtos') || request()->routeIs('gerenciamento_Produtos') ? 'active' : ''); ?>">Gerenciamento de produtos</a>
        </li>
        <?php endif; ?>

        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'CARDAPIO')): ?>
        <li class="nav-item">
            <span class="nav-link disabled">Cardápio</span>
        </li>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'ENTREGAS')): ?>
        <li class="nav-item">
            <a href="<?php echo e(route('entregas', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('entregas') ? 'active' : ''); ?>">Entregas</a>
        </li>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'MESAS')): ?>
        <li>
            <a href="<?php echo e(route('mesas.index', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('mesas.*') ? 'active' : ''); ?>">Mesas</a>
        </li>
        <?php endif; ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'GARCOM')): ?>
        <li class="nav-item">
            <a href="<?php echo e(route('garcom', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('garcom') ? 'active' : ''); ?>">Garçom</a>
        </li>
        <?php endif; ?>
    </ul>

    <?php else: ?>
    <ul class="nav nav-pills flex-column mb-auto ff-sidebar__nav">
        <li class="nav-item">
            <a href="<?php echo e(route('home', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('home') ? 'active' : ''); ?>">Principal</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo e(route('Lanches', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('Lanches') ? 'active' : ''); ?>">Lanches</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo e(route('Pizza', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('Pizza') ? 'active' : ''); ?>">Pizzas</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo e(route('Porcao', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('Porcao') ? 'active' : ''); ?>">Porção</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo e(route('Bebidas', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('Bebidas') ? 'active' : ''); ?>">Bebidas</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo e(route('pedidos', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('pedidos') ? 'active' : ''); ?>">Pedidos</a>
        </li>
        <li class="nav-item">
            <a href="<?php echo e(route('carrinho', [], false)); ?>" class="nav-link <?php echo e(request()->routeIs('carrinho') ? 'active' : ''); ?>">Carrinho</a>
        </li>
    </ul>
    <?php endif; ?>

    <div class="mt-auto ff-sidebar__footer">
        <?php if(isset($usuario) && $usuario): ?>
        <div class="dropdown w-100">
            <button class="btn ff-sidebar__user-btn w-100 d-flex align-items-center justify-content-between" type="button" aria-haspopup="true" aria-expanded="false">
                <div class="d-flex align-items-center me-2">
                    <div class="circulo_maior me-2">
                        <img class="profile-image" id="preview-image"
                            src="<?php echo e(isset($usuario['url_imagem_perfil']) && $usuario['url_imagem_perfil'] ? asset('img/perfil/' . $usuario['url_imagem_perfil']) : (isset($usuario->url_imagem_perfil) && $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.png'))); ?>"
                            alt="Foto do usuário">
                    </div>
                    <span class="text text-truncate ff-sidebar__user-name">
                        <?php echo e(is_array($usuario) ? ($usuario['nome'] ?? '') : ($usuario->primeiro_nome ?? ($usuario->nome ?? ''))); ?>

                    </span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end list w-100">
                <li><a class="dropdown-item text" href="<?php echo e(route('perfil', [], false)); ?>">Perfil</a></li>
                <li><a class="dropdown-item text" href="#">Configurações</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text" href="<?php echo e(route('logout', [], false)); ?>">Sair</a></li>
            </ul>
        </div>
        <?php else: ?>
        <a class="btn btn-light w-100" href="<?php echo e(route('login.form', [], false)); ?>">Entrar</a>
        <?php endif; ?>
    </div>
</nav>

<button type="button" class="ff-sidebar-overlay" data-sidebar-toggle aria-label="Fechar menu" tabindex="-1"></button>

<?php echo $__env->make('components.flash-toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script defer src="/js/mobile-interactions-fallback.js?v=<?php echo e(filemtime(public_path('js/mobile-interactions-fallback.js'))); ?>"></script>
<?php /**PATH C:\Users\omega\Downloads\TCC\FlashFood\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>
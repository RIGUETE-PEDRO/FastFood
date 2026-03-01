@php
    $isAdmin = request()->routeIs('Administrativo')
        || request()->routeIs('gerenciamento_*')
        || request()->routeIs('Pedidos.Administrativo')
        || request()->routeIs('gerenciamento_Produtos')
        || request()->routeIs('Cadastrar_Produto')
        || request()->routeIs('deletar_produto')
        || request()->routeIs('mesas.index');
@endphp

<nav class="ff-sidebar d-flex flex-column">
    <div class="ff-sidebar__brand d-flex align-items-center justify-content-between">
        <span class="ff-sidebar__logo">FlashFood</span>
        <button type="button" class="ff-sidebar__close" data-sidebar-toggle aria-label="Fechar menu">
            ✕
        </button>
    </div>

    @if ($isAdmin)
        <ul class="nav nav-pills flex-column mb-auto ff-sidebar__nav">
            <li class="nav-item">
                <a href="{{ route('Administrativo') }}" class="nav-link {{ request()->routeIs('Administrativo') ? 'active' : '' }}">DashBoard</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('gerenciamento_funcionarios') }}" class="nav-link {{ request()->routeIs('gerenciamento_funcionarios') ? 'active' : '' }}">Gerenciamento de Funcionários</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Pedidos.Administrativo') }}" class="nav-link {{ request()->routeIs('Pedidos.Administrativo') ? 'active' : '' }}">Pedidos</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('gerenciamento_produtos') }}" class="nav-link {{ request()->routeIs('gerenciamento_produtos') || request()->routeIs('gerenciamento_Produtos') ? 'active' : '' }}">Gerenciamento de produtos</a>
            </li>
            <li class="nav-item">
                <span class="nav-link disabled">Cardápio</span>
            </li>
            <li class="nav-item">
                <span class="nav-link disabled">Entregas</span>
            </li>
            <li>
                <a href="{{ route('mesas.index')}}" class="nav-link {{ request()->routeIs('mesas.index')|| request()->routeIs('mesas.index') ? 'active' : ''}}">Mesas</a>
            </li>
        </ul>
    @else
        <ul class="nav nav-pills flex-column mb-auto ff-sidebar__nav">
            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Principal</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Lanches') }}" class="nav-link {{ request()->routeIs('Lanches') ? 'active' : '' }}">Lanches</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Pizza') }}" class="nav-link {{ request()->routeIs('Pizza') ? 'active' : '' }}">Pizzas</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Porcao') }}" class="nav-link {{ request()->routeIs('Porcao') ? 'active' : '' }}">Porção</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Bebidas') }}" class="nav-link {{ request()->routeIs('Bebidas') ? 'active' : '' }}">Bebidas</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pedidos') }}" class="nav-link {{ request()->routeIs('pedidos') ? 'active' : '' }}">Pedidos</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('carrinho') }}" class="nav-link {{ request()->routeIs('carrinho') ? 'active' : '' }}">Carrinho</a>
            </li>

        </ul>
    @endif

    <div class="mt-auto ff-sidebar__footer">
        @if(isset($usuario) && $usuario)
            <div class="dropdown w-100">
                <button class="btn ff-sidebar__user-btn dropdown-toggle w-100 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center me-2">
                        <div class="circulo_maior me-2">
                            <img class="profile-image" id="preview-image"
                                 src="{{ isset($usuario['url_imagem_perfil']) && $usuario['url_imagem_perfil'] ? asset('img/perfil/' . $usuario['url_imagem_perfil']) : (isset($usuario->url_imagem_perfil) && $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.png')) }}"
                                 alt="Foto do usuário">
                        </div>
                        <span class="text text-truncate" style="max-width:120px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ is_array($usuario) ? ($usuario['nome'] ?? '') : ($usuario->primeiro_nome ?? ($usuario->nome ?? '')) }}
                        </span>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end list w-100">
                    <li><a class="dropdown-item text" href="{{ route('perfil') }}">Perfil</a></li>
                    <li><a class="dropdown-item text" href="#">Configurações</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text" href="{{ route('logout') }}">Sair</a></li>
                </ul>
            </div>
        @else
            <a class="btn btn-light w-100" href="{{ route('login.form') }}">Entrar</a>
        @endif
    </div>
    <script>
        document.addEventListener('click', function (event) {
            var toggle = event.target.closest('[data-sidebar-toggle]');
            if (!toggle) return;
            document.body.classList.toggle('ff-sidebar-collapsed');
        });
    </script>
</nav>

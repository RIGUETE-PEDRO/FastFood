@php
    $isAdmin = request()->routeIs('Administrativo')
        || request()->routeIs('admin.bemvindo')
        || request()->routeIs('admin.configuracoes')
        || request()->routeIs('gerenciamento_*')
        || request()->routeIs('Pedidos_Administrativo')
        || request()->routeIs('gerenciamento_Produtos')
        || request()->routeIs('Cadastrar_Produto')
        || request()->routeIs('deletar_produto')
        || request()->routeIs('mesas.*')
        || request()->routeIs('garcom')
        || request()->routeIs('entregas');

    $usuarioAtual = auth()->user() ?? ($usuario ?? null);
    $canSeeConfiguracoes = false;

    if ($usuarioAtual instanceof \App\Models\UsuarioModel) {
        $canSeeConfiguracoes = app(\App\Services\SecureKeyService::class)
            ->hasRole($usuarioAtual, \App\Roles\Roles::ADMIN);
    }

    $ffIcon = function (string $name) {
        $icons = [
            'dashboard' => '<path d="M4 13h7V4H4v9Z"/><path d="M13 20h7V4h-7v16Z"/><path d="M4 20h7v-5H4v5Z"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'orders' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
            'products' => '<path d="m21 16-9 5-9-5V8l9-5 9 5v8Z"/><path d="m3.3 7.3 8.7 4.9 8.7-4.9"/><path d="M12 22V12"/>',
            'cardapio' => '<path d="M4 19.5V5a2 2 0 0 1 2-2h11a3 3 0 0 1 3 3v13.5"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M8 7h7"/><path d="M8 11h8"/>',
            'delivery' => '<path d="M3 7h11v10H3z"/><path d="M14 10h4l3 3v4h-7"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
            'tables' => '<path d="M4 4h16v7H4z"/><path d="M8 11v9"/><path d="M16 11v9"/><path d="M6 20h4"/><path d="M14 20h4"/>',
            'garcom' => '<circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/><path d="m16 11 2 2 4-4"/>',
            'home' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/>',
            'lanches' => '<path d="M4 13h16"/><path d="M5 13a7 7 0 0 1 14 0"/><path d="M5 17h14"/><path d="M7 21h10a3 3 0 0 0 3-3H4a3 3 0 0 0 3 3Z"/>',
            'pizza' => '<path d="M12 2 3 21l18-7-9-12Z"/><circle cx="12" cy="10" r="1"/><circle cx="10" cy="15" r="1"/><circle cx="15" cy="14" r="1"/>',
            'porcao' => '<path d="M4 10h16l-2 10H6L4 10Z"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path d="M8 14h8"/>',
            'bebidas' => '<path d="M6 3h12l-1 18H7L6 3Z"/><path d="M7 8h10"/><path d="M10 3V1h4v2"/>',
            'cart' => '<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M2 3h3l2.2 11.4a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.6L20 8H6"/>',
        ];

        $path = $icons[$name] ?? $icons['orders'];

        return new \Illuminate\Support\HtmlString(
            '<span class="ff-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false">' . $path . '</svg></span>'
        );
    };
@endphp

<nav class="ff-sidebar d-flex flex-column" aria-label="Menu principal">
    <div class="ff-sidebar__brand d-flex align-items-center justify-content-between">
        <div class="ff-sidebar__brand-mark">
            <span class="ff-sidebar__logo-icon" aria-hidden="true">F</span>
            <span class="ff-sidebar__logo">FlashFood</span>
        </div>

        <span class="ff-sidebar__Versao">v1.0</span>

        <button type="button" class="ff-sidebar__close" data-sidebar-toggle aria-label="Fechar menu">
            &times;
        </button>
    </div>

    @if ($isAdmin)
        <ul class="nav nav-pills flex-column mb-auto ff-sidebar__nav">
            @role('DASHBORD')
                <li class="nav-item">
                    <a href="{{ route('Administrativo', [], false) }}" class="nav-link {{ request()->routeIs('Administrativo') ? 'active' : '' }}">
                        {!! $ffIcon('dashboard') !!}
                        <span>Dashboard</span>
                    </a>
                </li>
            @endrole

            @role('GERENCIAMENTO_FUNCIONARIOS')
                <li class="nav-item">
                    <a href="{{ route('gerenciamento_funcionarios', [], false) }}" class="nav-link {{ request()->routeIs('gerenciamento_funcionarios') ? 'active' : '' }}">
                        {!! $ffIcon('users') !!}
                        <span>Funcionarios</span>
                    </a>
                </li>
            @endrole

            @role('PEDIDOS')
                <li class="nav-item">
                    <a href="{{ route('Pedidos_Administrativo', [], false) }}" class="nav-link {{ request()->routeIs('Pedidos_Administrativo') ? 'active' : '' }}">
                        {!! $ffIcon('orders') !!}
                        <span>Pedidos</span>
                    </a>
                </li>
            @endrole

            @role('GERENCIAMENTO_PRODUTOS')
                <li class="nav-item">
                    <a href="{{ route('gerenciamento_produtos', [], false) }}" class="nav-link {{ request()->routeIs('gerenciamento_produtos') || request()->routeIs('gerenciamento_Produtos') ? 'active' : '' }}">
                        {!! $ffIcon('products') !!}
                        <span>Produtos</span>
                    </a>
                </li>
            @endrole

            @role('CARDAPIO')
                <li class="nav-item">
                    <span class="nav-link disabled">
                        {!! $ffIcon('cardapio') !!}
                        <span>Cardapio</span>
                    </span>
                </li>
            @endrole

            @role('ENTREGAS')
                <li class="nav-item">
                    <a href="{{ route('entregas', [], false) }}" class="nav-link {{ request()->routeIs('entregas') ? 'active' : '' }}">
                        {!! $ffIcon('delivery') !!}
                        <span>Entregas</span>
                    </a>
                </li>
            @endrole

            @role('MESAS')
                <li class="nav-item">
                    <a href="{{ route('mesas.index', [], false)}}" class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : ''}}">
                        {!! $ffIcon('tables') !!}
                        <span>Mesas</span>
                    </a>
                </li>
            @endrole

            @role('GARCOM')
                <li class="nav-item">
                    <a href="{{ route('garcom', [], false) }}" class="nav-link {{ request()->routeIs('garcom') ? 'active' : '' }}">
                        {!! $ffIcon('garcom') !!}
                        <span>Garcom</span>
                    </a>
                </li>
            @endrole
        </ul>
    @else
        <ul class="nav nav-pills flex-column mb-auto ff-sidebar__nav">
            <li class="nav-item">
                <a href="{{ route('home', [], false) }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    {!! $ffIcon('home') !!}
                    <span>Principal</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Lanches', [], false) }}" class="nav-link {{ request()->routeIs('Lanches') ? 'active' : '' }}">
                    {!! $ffIcon('lanches') !!}
                    <span>Lanches</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Pizza', [], false) }}" class="nav-link {{ request()->routeIs('Pizza') ? 'active' : '' }}">
                    {!! $ffIcon('pizza') !!}
                    <span>Pizzas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Porcao', [], false) }}" class="nav-link {{ request()->routeIs('Porcao') ? 'active' : '' }}">
                    {!! $ffIcon('porcao') !!}
                    <span>Porcao</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('Bebidas', [], false) }}" class="nav-link {{ request()->routeIs('Bebidas') ? 'active' : '' }}">
                    {!! $ffIcon('bebidas') !!}
                    <span>Bebidas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('pedidos', [], false) }}" class="nav-link {{ request()->routeIs('pedidos') ? 'active' : '' }}">
                    {!! $ffIcon('orders') !!}
                    <span>Pedidos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('carrinho', [], false) }}" class="nav-link {{ request()->routeIs('carrinho') ? 'active' : '' }}">
                    {!! $ffIcon('cart') !!}
                    <span>Carrinho</span>
                </a>
            </li>
        </ul>
    @endif

    <div class="mt-auto ff-sidebar__footer">
        @if(isset($usuario) && $usuario)
            <div class="dropdown w-100">
                <button class="btn ff-sidebar__user-btn w-100 d-flex align-items-center justify-content-between" type="button" aria-haspopup="true" aria-expanded="false">
                    <div class="d-flex align-items-center me-2">
                        <div class="circulo_maior me-2">
                            <img class="profile-image" id="preview-image"
                                src="{{ isset($usuario['url_imagem_perfil']) && $usuario['url_imagem_perfil'] ? asset('img/perfil/' . $usuario['url_imagem_perfil']) : (isset($usuario->url_imagem_perfil) && $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/perfil/personPadrao.svg')) }}"
                                alt="Foto do usuario">
                        </div>
                        <span class="text text-truncate ff-sidebar__user-name">
                            {{ is_array($usuario) ? ($usuario['nome'] ?? '') : ($usuario->primeiro_nome ?? ($usuario->nome ?? '')) }}
                        </span>
                    </div>
                    <span class="ff-sidebar__user-caret" aria-hidden="true">v</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end list w-100">
                    <li><a class="dropdown-item text" href="{{ route('perfil', [], false) }}">Editar perfil</a></li>
                    @if($canSeeConfiguracoes)
                        <li><a class="dropdown-item text" href="{{ route('admin.configuracoes', [], false) }}">Configuracoes</a></li>
                    @endif
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text" href="{{ route('logout', [], false) }}">Sair</a></li>
                </ul>
            </div>
        @else
            <a class="btn btn-light w-100" href="{{ route('login.form', [], false) }}">Entrar</a>
        @endif
    </div>
</nav>

<button type="button" class="ff-sidebar-overlay" data-sidebar-toggle aria-label="Fechar menu" tabindex="-1"></button>

@include('components.flash-toast')
<script defer src="/js/mobile-interactions-fallback.js?v={{ filemtime(public_path('js/mobile-interactions-fallback.js')) }}"></script>

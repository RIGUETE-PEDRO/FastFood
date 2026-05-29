<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Bem-vindo | Admin</title>
    @vite(['resources/js/app.js'])
    @include('partials.favicon')
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/BemVindo.css') }}">
</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>

            <main class="bemvindo">
                <section class="bemvindo-hero painel">
                    <div>
                        <span class="bemvindo-hero__tag">Painel administrativo</span>
                        <h1>Bem-vindo, {{ $nomeUsuario ?? 'Usuário' }}!</h1>
                        <p class="bemvindo-hero__subtitle">
                            Acesse rapidamente as áreas principais e acompanhe o fluxo do seu restaurante.
                        </p>
                    </div>
                    <div class="bemvindo-hero__badge">
                        <span>Perfil</span>
                        <strong>{{ $tipoUsuario ?? 'Usuário' }}</strong>
                    </div>
                </section>

                <section class="bemvindo-actions" aria-label="Acoes rapidas do administrativo">
                    @role('DASHBORD')
                    <a class="bemvindo-action" href="{{ route('Administrativo', [], false) }}">
                        <span class="bemvindo-action__icon">01</span>
                        <strong>Dashboard</strong>
                        <small>Indicadores de vendas, produtos e pedidos.</small>
                    </a>
                    @endrole

                    @role('PEDIDOS')
                    <a class="bemvindo-action" href="{{ route('Pedidos_Administrativo', [], false) }}">
                        <span class="bemvindo-action__icon">02</span>
                        <strong>Pedidos</strong>
                        <small>Acompanhe preparo, status e cupons.</small>
                    </a>
                    @endrole

                    @role('GERENCIAMENTO_PRODUTOS')
                    <a class="bemvindo-action" href="{{ route('gerenciamento_Produtos', [], false) }}">
                        <span class="bemvindo-action__icon">03</span>
                        <strong>Produtos</strong>
                        <small>Atualize cardapio, precos e disponibilidade.</small>
                    </a>
                    @endrole

                    @role('MESAS')
                    <a class="bemvindo-action" href="{{ route('mesas.index', [], false) }}">
                        <span class="bemvindo-action__icon">04</span>
                        <strong>Mesas</strong>
                        <small>Gerencie comandas e consumo no salao.</small>
                    </a>
                    @endrole
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

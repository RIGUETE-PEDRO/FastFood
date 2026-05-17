<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo | Admin</title>
    @vite(['resources/js/app.js'])
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

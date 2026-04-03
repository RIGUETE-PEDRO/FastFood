<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeyClock - Auditoria</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Keyclock.css') }}">
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">KeyClock</div>

            <nav class="kc-nav">
                <a href="{{ route('keyclock.index') }}" class="kc-link">Visão geral</a>

                <a href="{{ route('keyclock.grupo') }}" class="kc-link">Grupos</a>
                <a href="{{ route('keyclock.permissoes') }}" class="kc-link">Criar roles</a>
                <a href="{{ route('keyclock.auditoria') }}" class="kc-link active">Auditoria</a>
                <a href="{{ route('login.form') }}" class="kc-link kc-link-login">Voltar para login</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <h1>Auditoria</h1>
                <p>Rastreamento de eventos de autenticação e segurança.</p>
            </header>

            <section class="kc-card">
                <h2>Resumo</h2>
                <p>Visualize logs de login, alterações de permissão e alertas.</p>
            </section>
        </main>
    </div>
</body>

</html>

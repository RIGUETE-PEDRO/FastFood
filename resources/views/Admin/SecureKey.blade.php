<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>SecureKey</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/SecureKey.css') }}">
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">
                <span class="kc-brand__icon" aria-hidden="true">&#128274;</span>
                <span>SecureKey</span>
            </div>

            <nav class="kc-nav" aria-label="Navegacao SecureKey">
                <a href="{{ route('SecureKey.index') }}" class="kc-link active"><span aria-hidden="true">&#8962;</span>Visao geral</a>
                <a href="{{ route('SecureKey.grupo') }}" class="kc-link"><span aria-hidden="true">&#128101;</span>Grupos</a>
                <a href="{{ route('SecureKey.permissoes') }}" class="kc-link"><span aria-hidden="true">&#128273;</span>Criar roles</a>
                <a href="{{ route('SecureKey.auditoria') }}" class="kc-link"><span aria-hidden="true">&#128221;</span>Auditoria</a>
                <a href="{{ route('admin.bemvindo') }}" class="kc-link kc-link-login"><span aria-hidden="true">&#8592;</span>Voltar ao sistema</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <span class="kc-kicker">Modulo de seguranca</span>
                <h1>Painel SecureKey</h1>
                <p>Controle de acesso para roles, grupos e auditoria do sistema.</p>
            </header>

            <section class="kc-hero-card">
                <div class="kc-hero-card__icon" aria-hidden="true">&#128274;</div>
                <div>
                    <h2>Ambiente protegido</h2>
                    <p>Use este painel para revisar permissoes, manter grupos organizados e acompanhar eventos importantes de seguranca.</p>
                </div>
            </section>

            <section class="kc-overview-grid" aria-label="Atalhos do SecureKey">
                <a href="{{ route('SecureKey.grupo') }}" class="kc-overview-card">
                    <span class="kc-overview-card__icon" aria-hidden="true">&#128101;</span>
                    <strong>Grupos</strong>
                    <p>Associe roles aos perfis do sistema.</p>
                </a>
                <a href="{{ route('SecureKey.permissoes') }}" class="kc-overview-card">
                    <span class="kc-overview-card__icon" aria-hidden="true">&#128273;</span>
                    <strong>Roles</strong>
                    <p>Crie novas permissoes com clareza.</p>
                </a>
                <a href="{{ route('SecureKey.auditoria') }}" class="kc-overview-card">
                    <span class="kc-overview-card__icon" aria-hidden="true">&#128221;</span>
                    <strong>Auditoria</strong>
                    <p>Monitore acoes sensiveis em tempo real.</p>
                </a>
            </section>
        </main>
    </div>
</body>

</html>

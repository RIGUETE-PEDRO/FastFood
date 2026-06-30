<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>SecureKey - Permissoes</title>
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
                <a href="{{ route('SecureKey.index') }}" class="kc-link"><span aria-hidden="true">&#8962;</span>Visao geral</a>
                <a href="{{ route('SecureKey.grupo') }}" class="kc-link"><span aria-hidden="true">&#128101;</span>Grupos</a>
                <a href="{{ route('SecureKey.permissoes') }}" class="kc-link active"><span aria-hidden="true">&#128273;</span>Criar roles</a>
                <a href="{{ route('admin.bemvindo') }}" class="kc-link kc-link-login"><span aria-hidden="true">&#8592;</span>Voltar ao sistema</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <span class="kc-kicker">Chaves de acesso</span>
                <h1>Permissoes</h1>
                <p>Definicao de papeis, grupos e escopos de acesso.</p>
            </header>

            <form action="{{ route('SecureKey.permissoes.store') }}" method="post" class="kc-role-form">
                @csrf
                <section class="kc-card kc-card--form">
                    <div class="kc-form-group">
                        <label for="role_name">Role</label>
                        <input
                            type="text"
                            id="role_name"
                            name="role_name"
                            class="kc-role-input"
                            placeholder="Nome da role"
                            autocomplete="off"
                        >
                    </div>

                    <button type="submit" class="kc-btn kc-role-submit">Criar role</button>
                </section>
            </form>
        </main>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeyClock - Permissões</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Keyclock.css') }}">
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">KeyClock</div>

            <nav class="kc-nav">
                <a href="{{ route('keyclock.index') }}" class="kc-link">Visão geral</a>

                <a href="{{ route('keyclock.grupo') }}" class="kc-link">Grupos</a>
                <a href="{{ route('keyclock.permissoes') }}" class="kc-link active">Criar roles</a>
                <a href="{{ route('keyclock.auditoria') }}" class="kc-link">Auditoria</a>
                <a href="{{ route('login.form') }}" class="kc-link kc-link-login">Voltar para login</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <h1>Permissões</h1>
                <p>Definição de papéis, grupos e escopos de acesso.</p>
            </header>

            <form action="{{ route('keyclock.permissoes.store') }}" method="post" class="kc-role-form">
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

                    <button type="submit" class="kc-btn kc-role-submit">Criar Role</button>
                </section>
            </form>
        </main>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureKey</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/SecureKey.css') }}">
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">SecureKey</div>

            <nav class="kc-nav">
                <a href="{{ route('SecureKey.index') }}" class="kc-link active">Visão geral</a>
                <a href="{{ route('SecureKey.grupo') }}" class="kc-link">Grupos</a>
                <a href="{{ route('SecureKey.permissoes') }}" class="kc-link">Criar roles</a>
                <a href="{{ route('SecureKey.auditoria') }}" class="kc-link">Auditoria</a>
                <a href="{{ route('login.form') }}" class="kc-link kc-link-login">Voltar para login</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <h1>Painel SecureKey</h1>
                <p>Tela independente do administrativo, acessada apenas por URL.</p>
            </header>

            <section class="kc-card">
                <h2>Status da integração</h2>
                <p>Aqui podemos implementar controle de acesso por perfil de usuário e registrar auditorias.</p>
            </section>
        </main>
    </div>
</body>

</html>

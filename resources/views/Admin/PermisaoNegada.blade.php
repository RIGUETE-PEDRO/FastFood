<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permissão Negada</title>
    <link rel="stylesheet" href="{{ asset('css/PermissaoNegada.css') }}">
</head>

<body>
    <main class="denied-wrapper">
        <section class="denied-card">
            <div class="denied-badge">Código 403 • Acesso restrito</div>

            <h1 class="denied-title">Pare! Área restrita</h1>
            <p class="denied-text">
                Você tentou entrar em uma zona exclusiva para usuários com credenciais especiais.
                Para seguir, volte para o início ou utilize uma conta autorizada.
            </p>

            <div class="denied-actions">
                <a href="{{ route('home') }}" class="denied-button primary">Voltar ao início</a>
                <a href="{{ route('login.form') }}" class="denied-button outline">Entrar com outra conta</a>
            </div>

            <footer class="denied-footnote">
                Caso ache que isso é um engano, contate o suporte e informe o código <strong>403</strong>.
            </footer>
        </section>

        <aside class="denied-illustration" aria-hidden="true">
            <div class="police-lights">
                <span class="light blue"></span>
                <span class="light red"></span>
            </div>
            <img src="{{ asset('img/policial.svg') }}" alt="Ilustração de um policial" class="police-figure">
        </aside>
    </main>
</body>

</html>
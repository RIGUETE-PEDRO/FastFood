<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha Senha</title>
    <link rel="stylesheet" href="{{ asset('css/Login.css') }}">
</head>
<body>
   <div class="login-container">

    <div class="login-image">
        <img src="{{ asset('img/login-imagem.png') }}" alt="Recuperar Senha">
    </div>

    <div class="container">
        <h1>Recuperar Senha</h1>
        <p style="text-align: center; font-size: 14px; color: #666; margin-bottom: 20px;">
            Digite seu e-mail para receber as instruções de recuperação de senha.
        </p>

        @if (session('sucesso'))
            <div class="alert alert-success">
                {{ session('sucesso') }}
            </div>
        @endif

        @if (session('erro'))
            <div class="alert alert-danger">
                {{ session('erro') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('senha.recuperar') }}" method="POST">
            @csrf

            <div class="input">
                <label for="email" class="text">E-mail:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="seu@email.com">
            </div>

            <button type="submit">Enviar Link de Recuperação</button>

            <a href="/login" class="register">Voltar para Login</a>
        </form>
    </div>

</div>

</body>
</html>

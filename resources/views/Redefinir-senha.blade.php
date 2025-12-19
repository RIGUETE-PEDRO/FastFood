<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="{{ asset('css/Login.css') }}">
</head>
<body>
   <div class="login-container">

    <div class="login-image">
        <img src="{{ asset('img/login-imagem.png') }}" alt="Redefinir Senha">
    </div>

    <div class="container">
        <h1>Redefinir Senha</h1>
        <p style="text-align: center; font-size: 14px; color: #666; margin-bottom: 20px;">
            Digite sua nova senha abaixo.
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

        <form action="{{ route('senha.atualizar') }}" method="POST">
            @csrf
            
            <input type="hidden" name="token" value="{{ request('token') }}">
            <input type="hidden" name="email" value="{{ request('email') }}">
            
            <div class="input">
                <label for="password" class="text">Nova Senha:</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="input">
                <label for="password_confirmation" class="text">Confirmar Nova Senha:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
            </div>

            <button type="submit">Redefinir Senha</button>
            
            <a href="/login" class="register">Voltar para Login</a>
        </form>
    </div>

</div>

</body>
</html>

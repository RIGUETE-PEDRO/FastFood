<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    @vite(['resources/js/app.js'])
     <link rel="stylesheet" href="{{ asset('css/Login.css') }}">
</head>
<body>
   <div class="login-container">

    <div class="login-image">
        <img src="{{ asset('img/login-imagem.png') }}" alt="Login Image">
    </div>


    <div class="container">
        <div class="brand-mini" aria-label="Logo FlashFood">
            <img src="{{ asset('img/login-imagem.png') }}" alt="Logo FlashFood">
        </div>
        <h1>Login</h1>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="input">
                <label for="email" class="text">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input">
                <label for="senha" class="text">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
            <a href="/registro" class="register">Cadastre-se</a>
            <a href="/esqueci-senha" class="esquecer-senha">Esqueci minha senha</a>

        </form>
    </div>
</div>

@include('components.flash-toast')

</body>
</html>

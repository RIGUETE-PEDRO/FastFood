<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Cadastro</title>
     <link rel="stylesheet" href="{{ asset('css/Cadastro.css') }}">
</head>
<body>
   <div class="login-container">

    <div class="login-image">
        <img src="{{ asset('img/login-imagem.png') }}" alt="Login Image">
    </div>

    <div class="container">
        <h1>Cadastro</h1>



        <form action="{{ route('registro') }}" method="POST">
            @csrf

            <div class="input">
                <label for="name" class="text">Nome Completo:</label>
                <input type="text" id="name" name="nome" value="{{ old('nome') }}" required>
            </div>

             <div class="input">
                <label for="phone" class="text">Telefone:</label>
                <input type="text" id="phone" name="telefone" value="{{ old('telefone') }}" required>
            </div>

            <div class="input">
                <label for="email" class="text">E-mail:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="input">
                <label for="password" class="text">Senha:</label>
                <input type="password" name="senha" required>

            </div>

            <div class="input">
                <label for="password_confirmation" class="text">Confirme a Senha:</label>
                <input type="password" id="password_confirmation" name="senha_confirmation" required>
            </div>


            <button type="submit">Cadastrar</button>
            <a href="/login" class="register">Já possui uma conta? Faça login</a>

    @if (session('sucesso'))
        <label class="msg-sucesso">{{ session('sucesso') }}</label>
    @endif


    @if (session('erro'))
    <div class="msg-erro">
        <img src="{{ asset('img/alert.png') }}" alt="Erro">
        <span>{{ session('erro') }}</span>
    </div>
@endif


        </form>
    </div>

    </div>

</body>
</html>

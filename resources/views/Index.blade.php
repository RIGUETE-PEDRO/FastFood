<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-body-tertiary navbar">
        <div class="container-fluid navbar">
            <a class="navbar-brand text titulo" href="#">FlashFood</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll navbar" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Principal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text" aria-current="page" href="#">Lanches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Pizzas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Bebidas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Entregas</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text navegador" href="#">Carrinho</a>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true"></a>
                    </li>
                </ul>

                <!-- Área de ações/auth (alinhada à direita) -->
                <!-- largura fixa para evitar mudança de layout quando alterna entre logado/deslogado -->
                <div class="d-flex align-items-center ms-auto" style="width:220px; max-width:220px; flex-shrink:0; justify-content:flex-end; gap:8px;">
                    @auth
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="circulo_maior me-2">
                                <img class="profile-image" id="preview-image"
                                    src="{{ $usuario['url_imagem_perfil'] ? asset('img/perfil/' . $usuario['url_imagem_perfil']) : asset('img/person.png') }}"
                                    alt="Foto do usuário" style="width:36px;height:36px;object-fit:cover;border-radius:50%;">
                            </div>
                            <span class="text text-truncate" style="max-width:120px;display:inline-block;vertical-align:middle;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $usuario['nome'] }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end list">
                            <li><a class="dropdown-item text" href="{{ route('perfil') }}">Perfil</a></li>
                            <li><a class="dropdown-item text" href="#">Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text" href="{{ route('logout') }}">Sair</a></li>
                        </ul>
                    </div>
                    @endauth

                    @guest
                    <a class="btn btn-primary rounded-pill px-3 py-1 ms-3" href="{{ route('login.form') }}">Entrar</a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

</body>

</html>

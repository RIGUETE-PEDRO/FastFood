<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pedidos</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Pedido.css') }}">
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
                        <a class="nav-link text navegador" href="{{ route('home') }}">Principal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active text" aria-current="page" href="{{ route('Lanches') }}">Lanches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('Pizza') }}">Pizzas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('Porcao') }}">Porção</a>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('Bebidas') }}">Bebidas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Pedidos</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link text navegador" href="{{ route('carrinho') }}">Carrinho</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true"></a>
                    </li>
                </ul>

                <!-- Área de ações/auth (alinhada à direita) -->
                <!-- largura fixa para evitar mudança de layout quando alterna entre logado/deslogado -->
                <div class="d-flex align-items-center ms-auto" style="width:220px; max-width:220px; flex-shrink:0; justify-content:flex-end; gap:8px;">
                    @if(!empty($usuario))
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="circulo_maior me-2">
                                <img class="profile-image" id="preview-image"
                                    src="{{ isset($usuario['url_imagem_perfil']) && $usuario['url_imagem_perfil'] ? asset('img/perfil/' . $usuario['url_imagem_perfil']) : asset('img/person.png') }}"
                                    alt="Foto do usuário">
                            </div>
                            <span class="text text-truncate" style="max-width:120px;display:inline-block;vertical-align:middle;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ is_array($usuario) ? $usuario['nome'] : ($usuario->nome ?? '') }}</span>
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
                    @else
                    <a class="btn btn-primary rounded-pill px-3 py-1 ms-3" href="{{ route('login.form') }}">Entrar</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="container mt-4 conteinner-pedidos">
            <h1>Meus Pedidos</h1>

    @if($pedidos->isEmpty())
        <p>Você não possui pedidos.</p>
    @else
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Itens</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->id }}</td>
                        <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $pedido->status }}</td>
                        <td>R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                        <td>
                            <ul>
                                @foreach($pedido->produto as $produtos)
                                    <li>{{ $produtos->nome }} x {{ $produtos->pivot->quantidade }} (R$ {{ number_format($produtos->pivot->preco_unitario, 2, ',', '.') }})</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

            
        </div>
    </main>
</body>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lanches</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
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
                        <a class="nav-link active text" aria-current="page" href="#">Lanches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('Pizza') }}">Pizzas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('Porcao') }}">Porção</a>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Bebidas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Pedidos</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link text navegador" href="#">Carrinho</a>
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


        <div class="container-produtos mt-4">
            @foreach ($lanches as $lanche)

            <div class="produto" data-produto-id="{{ $lanche->id }}" data-produto-nome="{{ $lanche->nome }}" data-produto-preco="{{ $lanche->preco }}">
                <div class="container-img">
                    <img src="{{ asset('img/produtos/' . $lanche->imagem_url) }}" alt="">
                </div>
                <div class="preco-conteiner">
                    <label class="lanche">{{ $lanche->nome }}</label>
                    <label class="preco">Preço</label>
                    <br>
                    <label class="valor">R${{ $lanche->preco }}</label>
                    <br>
                </div>
                @if(!empty($lanche->descricao))
                <div class="ingredientes-wrap">
                    <span class="ingredientes">ingredientes:</span>
                    <div class="ingredientes-lista">{{ $lanche->descricao }}</div>
                </div>
                @else
                <br>
                <br>
                @endif
                <div>
                    <button type="button" class="button-adicionar">Adicionar ao carrinho</button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Modal: Adicionar ao carrinho -->
        <div class="modal fade " id="addToCartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content conteiner-info">
                    <form method="POST" action="{{ route('carrinho.adicionar') }}">
                        <div class="modal-header">
                            <h5 class="modal-title text_modal">Adicionar ao carrinho</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="cart_produto_id" name="produto_id" value="">
                            <input type="hidden" id="cart_preco" name="preco" value="">

                            <div class="mb-3">
                                <label class="form-label text_modal">Produto</label>
                                <input type="text" class="form-control" id="cart_produto_nome" value="" readonly disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text_modal" for="cart_quantidade">Quantidade</label>
                                <input type="number" class="form-control" id="cart_quantidade" name="quantidade" min="1" value="1" required>
                            </div>

                            <div class="mb-0 ">
                                <label class="form-label text_modal" for="cart_observacao">Observação (opcional)</label>
                                <textarea class="form-control" id="cart_observacao" name="observacao" rows="3" placeholder="Ex: sem cebola, bem passado..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancelar " data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-confirmar">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>

</body>

</html>

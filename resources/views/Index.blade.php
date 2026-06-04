<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}?v={{ filemtime(public_path('css/Admin/Principal.css')) }}">

    <link rel="stylesheet" href="{{ asset('css/Index.css') }}?v={{ filemtime(public_path('css/Index.css')) }}">
</head>

<body>

    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>
    <main>

        <!-- CARROSSEL -->
        <div class="carousel-produtos">
            <div class="carousel-track">
                @foreach ($produtos as $produto)
                <div class="produto-card-mini" data-produto-id="{{ $produto->id }}" data-produto-nome="{{ $produto->nome }}" data-produto-preco="{{ $produto->preco }}">
                    <div class="mini-img">
                        <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="{{ $produto->nome }}">
                    </div>

                    <div class="mini-info">
                        <span class="mini-titulo">{{ $produto->nome }}</span>
                        <span class="mini-preco">
                    R$ {{ number_format((float) $produto->preco, 2, ',', '.') }}
                </span>
                    </div>
                </div>
                @endforeach


                @foreach ($produtos as $produto)
                <div class="produto-card-mini" data-produto-id="{{ $produto->id }}" data-produto-nome="{{ $produto->nome }}" data-produto-preco="{{ $produto->preco }}">
                    <div class="mini-img">
                        <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="{{ $produto->nome }}">
                    </div>

                    <div class="mini-info">
                        <span class="mini-titulo">{{ $produto->nome }}</span>
                        <span class="mini-preco">
                    R$ {{ number_format((float) $produto->preco, 2, ',', '.') }}
                </span>
                    </div>
                </div>
                @endforeach

            </div>
        </div>




        <section class="home-products" aria-labelledby="home-products-title">
            <div class="home-products__header">
                <div>
                    <span class="home-products__eyebrow">Produtos</span>
                    <h1 id="home-products-title">Escolha seu pedido</h1>
                    <p>Produtos em destaque com preco, ingredientes e acao rapida para adicionar ao carrinho.</p>
                </div>
                <span class="home-products__count">{{ $produtos->count() }} itens</span>
            </div>

        <div class="container-produtos mt-4">
            @foreach ($produtos as $produto)

            <div class="produto produto--interactive" data-produto-id="{{ $produto->id }}" data-produto-nome="{{ $produto->nome }}" data-produto-preco="{{ $produto->preco }}">
                <div class="container-img">
                    <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="{{ $produto->nome }}" loading="lazy">
                    <span class="produto-badge" aria-label="Preço">R$ {{ number_format((float) $produto->preco, 2, ',', '.') }}</span>
                </div>
                <div class="produto-body">
                    <div class="preco-conteiner">
                        <h2 class="lanche">{{ $produto->nome }}</h2>
                    </div>
                @if(!empty($produto->descricao))
                <div class="ingredientes-wrap">
                    <span class="ingredientes">Ingredientes</span>
                    <div class="ingredientes-lista">{{ $produto->descricao }}</div>
                </div>
                @else
                <p class="produto-empty-description">Descricao nao informada.</p>
                @endif
                <div class="produto-action">
                    <button type="button" class="button-adicionar">Adicionar ao carrinho</button>
                </div>
                </div>
            </div>
            @endforeach
        </div>
        </section>

        <!-- Modal: Adicionar ao carrinho -->
        <div class="modal fade " id="addToCartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content conteiner-info">
                    <form method="POST" action="{{ route('carrinho.adicionar') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title text_modal">Adicionar ao carrinho</h5>

                            <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal" aria-label="Fechar"></button>
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
        </div>
    </div>
@include('components.flash-toast')
<script src="{{ asset('js/carousel.js') }}"></script>
</body>

</html>

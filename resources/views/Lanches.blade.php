<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lanches</title>
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


        <section class="home-products" aria-labelledby="lanches-title">
            <div class="home-products__header">
                <div>
                    <span class="home-products__eyebrow">Produtos</span>
                    <h1 id="lanches-title">Lanches</h1>
                    <p>Escolha seu lanche favorito e adicione ao carrinho.</p>
                </div>
                <span class="home-products__count">{{ $lanches->count() }} itens</span>
            </div>

        <div class="container-produtos mt-4">
            @foreach ($lanches as $lanche)

            <div class="produto produto--interactive" data-produto-id="{{ $lanche->id }}" data-produto-nome="{{ $lanche->nome }}" data-produto-preco="{{ $lanche->preco }}">
                <div class="container-img">
                    <img src="{{ asset('img/produtos/' . $lanche->imagem_url) }}" alt="{{ $lanche->nome }}" loading="lazy">
                    <span class="produto-badge" aria-label="Preço">R$ {{ number_format((float) $lanche->preco, 2, ',', '.') }}</span>
                </div>
                <div class="produto-body">
                    <div class="preco-conteiner">
                        <h2 class="lanche">{{ $lanche->nome }}</h2>
                    </div>
                @if(!empty($lanche->descricao))
                <div class="ingredientes-wrap">
                    <span class="ingredientes">Ingredientes</span>
                    <div class="ingredientes-lista">{{ $lanche->descricao }}</div>
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
        </div>
    </div>
    @include('components.flash-toast')
</body>

</html>

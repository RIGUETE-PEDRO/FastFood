<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pizzas</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}?v={{ filemtime(public_path('css/Admin/Principal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}?v={{ filemtime(public_path('css/Index.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/PersonalizacaoProduto.css') }}">
</head>

<body>

    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>
    <main>


        <section class="home-products" aria-labelledby="pizzas-title">
            <div class="home-products__header">
                <div>
                    <span class="home-products__eyebrow">Produtos</span>
                    <h1 id="pizzas-title">Pizzas</h1>
                    <p>Sabores, ingredientes e pedido rápido.</p>
                    <button type="button" class="pizza-prices-trigger" data-pizza-prices-open>
                        Ver tamanhos e preços
                    </button>
                </div>
                <span class="home-products__count">{{ $pizzas->count() }} itens</span>
            </div>

        <div class="container-produtos mt-4">
            @foreach ($pizzas as $pizza)


            <div class="produto produto--interactive" data-produto-id="{{ $pizza->id }}" data-produto-nome="{{ $pizza->nome }}" data-produto-preco="{{ $pizza->preco }}" data-produto-tipo="pizza" data-precos='{{ json_encode($pizza->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) }}' data-bordas='{{ json_encode($pizza->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>
                <div class="container-img">
                    @if(filled($pizza->imagem_url))
                        <img src="{{ asset('img/produtos/' . $pizza->imagem_url) }}" alt="{{ $pizza->nome }}" @if($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async">
                    @else
                        <div class="produto-imagem-placeholder" role="img" aria-label="Imagem indisponível para {{ $pizza->nome }}">Imagem indisponível</div>
                    @endif
                </div>
                <div class="produto-body">
                    <div class="preco-conteiner">
                        <h2 class="lanche">{{ $pizza->nome }}</h2>
                    </div>
                @if(!empty($pizza->descricao))
                <div class="ingredientes-wrap">
                    <span class="ingredientes">Ingredientes</span>
                    <div class="ingredientes-lista">{{ $pizza->descricao }}</div>
                </div>
                @else
                <p class="produto-empty-description">Descrição não informada.</p>
                @endif
                <div class="produto-action">
                    <button type="button" class="button-adicionar">Adicionar ao carrinho</button>
                </div>
                </div>
            </div>
            @endforeach
        </div>
        </section>

        @include('partials.modal-precos-pizza')
        @include('partials.modal-carrinho-personalizacao', ['pizzas' => $pizzas])
    </main>
        </div>
    </div>
@include('components.flash-toast')
</body>

</html>

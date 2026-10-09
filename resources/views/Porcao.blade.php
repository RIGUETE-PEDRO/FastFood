<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Porção</title>
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


        <section class="home-products home-products--porcoes" aria-labelledby="porcoes-title">
            <div class="home-products__header">
                <div>
                    <span class="home-products__eyebrow">Produtos</span>
                    <h1 id="porcoes-title">Porções</h1>
                    <p>Porções para compartilhar ou acompanhar seu pedido.</p>
                </div>
                <span class="home-products__count">{{ $porcao->count() }} itens</span>
            </div>

        <div class="container-produtos mt-4">
            @forelse ($porcao as $porcaos)


            <div class="produto produto--interactive" data-produto-id="{{ $porcaos->id }}" data-produto-nome="{{ $porcaos->nome }}" data-produto-preco="{{ $porcaos->preco }}" data-produto-tipo="porcao" data-aceita-meia="{{ $porcaos->aceita_meia_por ? '1' : '0' }}" data-preco-meia="{{ $porcaos->preco_meia_por }}">
                <div class="container-img">
                    @if(filled($porcaos->imagem_url))
                        <img src="{{ asset('img/produtos/' . $porcaos->imagem_url) }}" alt="{{ $porcaos->nome }}" @if($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async">
                    @else
                        <div class="produto-imagem-placeholder" role="img" aria-label="Imagem indisponível para {{ $porcaos->nome }}">Imagem indisponível</div>
                    @endif
                </div>
                <div class="produto-body">
                    <div class="preco-conteiner">
                        <h2 class="lanche">{{ $porcaos->nome }}</h2>
                    </div>
                    <span class="porcao-preco">R$ {{ number_format((float) $porcaos->preco, 2, ',', '.') }}</span>
                 @if(!empty($porcaos->descricao))
                <div class="ingredientes-wrap">
                    <span class="ingredientes">Ingredientes</span>
                    <div class="ingredientes-lista">{{ $porcaos->descricao }}</div>
                </div>
                @else
                <p class="produto-empty-description">Descrição não informada.</p>
                @endif
                <div class="produto-action">
                    <button type="button" class="button-adicionar">Adicionar ao carrinho</button>
                </div>
                </div>
            </div>
            @empty
                <div class="alert alert-warning" role="alert">
                    Nenhuma porção disponível no momento.
                </div>
            @endforelse
        </div>
        </section>

        @include('partials.modal-carrinho-personalizacao', ['pizzas' => $pizzas])
    </main>
        </div>
    </div>
@include('components.flash-toast')
</body>

</html>

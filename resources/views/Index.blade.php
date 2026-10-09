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
    <link rel="stylesheet" href="{{ asset('css/PersonalizacaoProduto.css') }}">
</head>

<body class="page-loading">
    <div class="page-loader" role="status" aria-live="polite" aria-label="Carregando imagens">
        <span class="page-loader__spinner" aria-hidden="true"></span>
        <span>Carregando cardápio...</span>
    </div>

    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>
    <main>

        @php
            $produtosPizza = $produtos->filter(fn ($produto) => in_array(mb_strtolower(\Illuminate\Support\Str::ascii(optional($produto->categoria)->nome)), ['pizza', 'pizzas'], true));
        @endphp

        <!-- CARROSSEL: somente produtos marcados como destaque -->
        @if ($produtosDestaque->isNotEmpty())
        <div class="carousel-produtos">
            <div class="carousel-track">
                @foreach ($produtosDestaque as $produto)
                @php $categoriaProduto = mb_strtolower(\Illuminate\Support\Str::ascii(optional($produto->categoria)->nome)); @endphp
                @php $ehPizza = in_array($categoriaProduto, ['pizza', 'pizzas'], true); @endphp
                @php $precoLista = $ehPizza ? null : (float) $produto->preco; @endphp
                <div class="produto-card-mini" data-produto-id="{{ $produto->id }}" data-produto-nome="{{ $produto->nome }}" data-produto-preco="{{ $produto->preco }}" data-produto-tipo="{{ in_array($categoriaProduto, ['pizza', 'pizzas'], true) ? 'pizza' : (in_array($categoriaProduto, ['porcao', 'porcoes'], true) ? 'porcao' : '') }}" data-aceita-meia="{{ $produto->aceita_meia_por ? '1' : '0' }}" data-preco-meia="{{ $produto->preco_meia_por }}" data-precos='{{ json_encode(in_array($categoriaProduto, ['pizza', 'pizzas'], true) ? ($produto->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) : []) }}' data-bordas='{{ json_encode($produto->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>
                    <div class="mini-img">
                        @if(filled($produto->imagem_url))
                            <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="{{ $produto->nome }}" decoding="async" fetchpriority="high">
                        @else
                            <div class="mini-img-placeholder" role="img" aria-label="Imagem indisponível para {{ $produto->nome }}">Sem imagem</div>
                        @endif
                    </div>

                    <div class="mini-info">
                        <span class="mini-titulo">{{ $produto->nome }}</span>
                        @unless($ehPizza)
                            <span class="mini-preco">R$ {{ number_format((float) $precoLista, 2, ',', '.') }}</span>
                        @endunless
                    </div>
                </div>
                @endforeach


                @foreach ($produtosDestaque as $produto)
                @php $categoriaProduto = mb_strtolower(\Illuminate\Support\Str::ascii(optional($produto->categoria)->nome)); @endphp
                @php $ehPizza = in_array($categoriaProduto, ['pizza', 'pizzas'], true); @endphp
                @php $precoLista = $ehPizza ? null : (float) $produto->preco; @endphp
                <div class="produto-card-mini" data-produto-id="{{ $produto->id }}" data-produto-nome="{{ $produto->nome }}" data-produto-preco="{{ $produto->preco }}" data-produto-tipo="{{ in_array($categoriaProduto, ['pizza', 'pizzas'], true) ? 'pizza' : (in_array($categoriaProduto, ['porcao', 'porcoes'], true) ? 'porcao' : '') }}" data-aceita-meia="{{ $produto->aceita_meia_por ? '1' : '0' }}" data-preco-meia="{{ $produto->preco_meia_por }}" data-precos='{{ json_encode(in_array($categoriaProduto, ['pizza', 'pizzas'], true) ? ($produto->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) : []) }}' data-bordas='{{ json_encode($produto->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>
                    <div class="mini-img">
                        @if(filled($produto->imagem_url))
                            <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="{{ $produto->nome }}" loading="lazy" decoding="async">
                        @else
                            <div class="mini-img-placeholder" role="img" aria-label="Imagem indisponível para {{ $produto->nome }}">Sem imagem</div>
                        @endif
                    </div>

                    <div class="mini-info">
                        <span class="mini-titulo">{{ $produto->nome }}</span>
                        @unless($ehPizza)
                            <span class="mini-preco">R$ {{ number_format((float) $precoLista, 2, ',', '.') }}</span>
                        @endunless
                    </div>
                </div>
                @endforeach

            </div>
        </div>
        @endif




        <section class="home-products" aria-labelledby="home-products-title">
            <div class="home-products__header">
                <div>
                    <span class="home-products__eyebrow">Produtos</span>
                    <h1 id="home-products-title">Escolha seu pedido</h1>
                    <p>Confira ingredientes, consulte os preços das pizzas e adicione seus produtos ao carrinho.</p>
                    @if($produtosPizza->isNotEmpty())
                        <button type="button" class="pizza-prices-trigger" data-pizza-prices-open>
                            Ver tamanhos e preços das pizzas
                        </button>
                    @endif
                </div>
                <span class="home-products__count">{{ $produtos->count() }} itens</span>
            </div>

        <div class="container-produtos mt-4">
            @foreach ($produtos as $produto)

            @php $categoriaProduto = mb_strtolower(\Illuminate\Support\Str::ascii(optional($produto->categoria)->nome)); @endphp
            @php $ehPizza = in_array($categoriaProduto, ['pizza', 'pizzas'], true); @endphp
            @php $precoLista = $ehPizza ? null : (float) $produto->preco; @endphp
            <div class="produto produto--interactive" data-produto-id="{{ $produto->id }}" data-produto-nome="{{ $produto->nome }}" data-produto-preco="{{ $produto->preco }}" data-produto-tipo="{{ in_array($categoriaProduto, ['pizza', 'pizzas'], true) ? 'pizza' : (in_array($categoriaProduto, ['porcao', 'porcoes'], true) ? 'porcao' : '') }}" data-aceita-meia="{{ $produto->aceita_meia_por ? '1' : '0' }}" data-preco-meia="{{ $produto->preco_meia_por }}" data-precos='{{ json_encode(in_array($categoriaProduto, ['pizza', 'pizzas'], true) ? ($produto->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) : []) }}' data-bordas='{{ json_encode($produto->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>
                <div class="container-img">
                    @if(filled($produto->imagem_url))
                        <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="{{ $produto->nome }}" @if($loop->first) loading="eager" fetchpriority="high" @else loading="lazy" @endif decoding="async">
                    @else
                        <div class="produto-imagem-placeholder" role="img" aria-label="Imagem indisponível para {{ $produto->nome }}">Imagem indisponível</div>
                    @endif
                    @unless($ehPizza)
                        <span class="produto-badge" aria-label="Preço">R$ {{ number_format((float) $precoLista, 2, ',', '.') }}</span>
                    @endunless
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

        @if($produtosPizza->isNotEmpty())
            @include('partials.modal-precos-pizza')
        @endif
        @include('partials.modal-carrinho-personalizacao', ['pizzas' => $produtosPizza])

    </main>
        </div>
    </div>
@include('components.flash-toast')
<script src="{{ asset('js/carousel.js') }}"></script>
<script>
    (() => {
        const imagensPrioritarias = [...document.querySelectorAll('.carousel-produtos img, .home-products img')]
            .filter((imagem) => imagem.loading !== 'lazy');

        const aguardarImagem = (imagem) => {
            if (imagem.complete) return Promise.resolve();

            return new Promise((concluir) => {
                imagem.addEventListener('load', concluir, { once: true });
                imagem.addEventListener('error', concluir, { once: true });
            });
        };

        Promise.all(imagensPrioritarias.map(aguardarImagem)).finally(() => {
            document.body.classList.remove('page-loading');
        });

        // Mantém a página acessível caso a conexão fique indisponível.
        window.setTimeout(() => document.body.classList.remove('page-loading'), 8000);
    })();
</script>
</body>

</html>

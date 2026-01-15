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
                <p class="text-muted">Você não possui pedidos.</p>
            @else
                <div class="lista-pedidos mt-3">
                    @foreach($pedidos as $pedido)
                        @php
                            $statusTexto = strtoupper((string) optional($pedido->statusRelacionamento)->status);
                            $statusClasse = match ($statusTexto) {
                                'PENDENTE' => 'badge-status badge-status--pendente',
                                'EM PREPARO', 'PREPARANDO' => 'badge-status badge-status--preparo',
                                'ENTREGUE' => 'badge-status badge-status--entregue',
                                'CANCELADO' => 'badge-status badge-status--cancelado',
                                default => 'badge-status badge-status--padrao',
                            };
                        @endphp

                        <article class="pedido-card">
                            <header class="pedido-card__header">
                                <div>
                                    <h2 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h2>
                                    <span class="pedido-card__subtitulo">Realizado em {{ optional($pedido->created_at)->format('d/m/Y \à\s H:i') ?? 'N/D' }}</span>
                                </div>
                                <span class="{{ $statusClasse }}">{{ $statusTexto !== '' ? $statusTexto : 'STATUS INDEFINIDO' }}</span>
                            </header>

                            <section class="pedido-card__secao">
                                <h3 class="pedido-card__secao-titulo">Resumo</h3>
                                <dl class="pedido-dados">
                                    <div>
                                        <dt>Método de pagamento</dt>
                                        <dd>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Não informado' }}</dd>
                                    </div>
                                    <div>
                                        <dt>Valor total</dt>
                                        <dd>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="pedido-card__secao">
                                <h3 class="pedido-card__secao-titulo">Endereço de entrega</h3>
                                <dl class="pedido-endereco">
                                    <div>
                                        <dt>Logradouro</dt>
                                        <dd>{{ optional($pedido->endereco)->logradouro ?? 'Não informado' }}</dd>
                                    </div>
                                    <div>
                                        <dt>Número</dt>
                                        <dd>{{ optional($pedido->endereco)->numero ?? 's/n' }}</dd>
                                    </div>
                                    <div>
                                        <dt>Bairro</dt>
                                        <dd>{{ optional($pedido->endereco)->bairro ?? 'Não informado' }}</dd>
                                    </div>
                                    <div>
                                        <dt>Complemento</dt>
                                        <dd>{{ optional($pedido->endereco)->complemento ?? '—' }}</dd>
                                    </div>
                                    <div>
                                        <dt>Cidade</dt>
                                        <dd>{{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Não informado' }}</dd>
                                    </div>
                                </dl>
                            </section>

                            @if($pedido->itens->isNotEmpty())
                                <section class="pedido-card__secao">
                                    <h3 class="pedido-card__secao-titulo">Itens do pedido</h3>
                                    <ul class="pedido-itens">
                                        @foreach($pedido->itens as $item)
                                            <li class="pedido-itens__linha">
                                                <div>
                                                    <span class="pedido-itens__titulo">{{ optional($item->produto)->nome ?? 'Produto removido' }}</span>
                                                    <span class="pedido-itens__detalhe">{{ $item->quantidade }}× R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
                                                </div>
                                                <strong>R$ {{ number_format((float) $item->quantidade * (float) $item->preco_unitario, 2, ',', '.') }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif


        </div>
    </main>
</body>
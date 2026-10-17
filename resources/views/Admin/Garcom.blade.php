<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>Painel do Garçom</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}?v={{ filemtime(public_path('css/Admin/Principal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/GerenciamentoProduto.css') }}?v={{ filemtime(public_path('css/Admin/GerenciamentoProduto.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Garcom.css') }}?v={{ filemtime(public_path('css/Admin/Garcom.css')) }}">
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
                <section class="dashboard-header">
                    <h1>Painel do Garçom</h1>
                    <p>Monte o pedido, selecione a mesa e envie tudo para a comanda.</p>
                </section>

                <section class="dashboard-header garcom-order-panel">
                    <div class="garcom-order-head">
                        <div>
                            <h5 class="page-title">Pedido da mesa</h5>
                            <label class="form-label" for="mesa_id">Mesa</label>
                            <select name="mesa_id" id="mesa_id" class="form-select" required>
                                <option value="">Selecione</option>
                                @foreach ($mesas as $mesa)
                                <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero_da_mesa }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="garcom-order-total">
                            <span>Total</span>
                            <strong id="garcomOrderTotal">R$ 0,00</strong>
                            <small id="garcomOrderCount">0 itens</small>
                        </div>
                    </div>

                    <div class="garcom-order-list" id="garcomOrderList" aria-live="polite"></div>
                    <div class="garcom-order-empty" id="garcomOrderEmpty">Nenhum produto adicionado.</div>

                    <form action="{{ route('garcom.adicionar-produto') }}" method="POST" id="garcomOrderForm" class="garcom-order-form">
                        @csrf
                        <input type="hidden" name="mesa_id" id="garcomOrderMesaId">
                        <div id="garcomOrderInputs"></div>

                        <div class="garcom-order-actions">
                            <button type="button" class="garcom-order-clear" id="garcomLimparPedido" disabled>Limpar lista</button>
                            <button type="submit" class="btn-add" id="garcomEnviarPedido" disabled>Adicionar lista a mesa</button>
                        </div>
                    </form>
                </section>

                <div class="gerenciamento-container">
                    <div class="header-section">
                        <h1 class="page-title">Produtos</h1>

                    </div>
                    <div class="table-card">
                        <div class="table-header">
                            <h2>Lista de Produtos</h2>
                            <div class="search-box">
                                <input type="text" id="searchInput" class="search-input" placeholder="Buscar produto...">
                            </div>
                        </div>

                        <div class="garcom-filter-wrap">
                            <form method="GET" class="garcom-filter-form" aria-label="Filtro por categoria">
                                <div class="garcom-filter-field">
                                    <label for="categoria_id" class="garcom-filter-label">Categoria</label>
                                    <select name="categoria_id" id="categoria_id" class="garcom-filter-select">
                                        <option value="">Todas as categorias</option>
                                        @foreach(($categorias ?? []) as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="garcom-filter-actions">
                                    <button type="submit" class="btn-add garcom-filter-btn">Filtrar</button>
                                    <button type="button" class="garcom-filter-clear">Limpar</button>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="produtos-table garcom-produtos-table">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>Nome</th>
                                        <th>Preço</th>
                                        <th>Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    @foreach($produtos as $produto)
                                    @php
                                        $categoriaProduto = mb_strtolower(\Illuminate\Support\Str::ascii(trim((string) optional($produto->categoria)->nome)));
                                        $tipoProduto = in_array($categoriaProduto, ['pizza', 'pizzas'], true)
                                            ? 'pizza'
                                            : (in_array($categoriaProduto, ['porcao', 'porcoes'], true) ? 'porcao' : '');
                                    @endphp
                                    <tr
                                        data-produto-id="{{ $produto->id }}"
                                        data-produto-nome="{{ $produto->nome }}"
                                        data-produto-preco="{{ (float) $produto->preco }}"
                                        data-produto-tipo="{{ $tipoProduto }}"
                                        data-aceita-meia="{{ $produto->aceita_meia_por ? '1' : '0' }}"
                                        data-preco-meia="{{ $produto->preco_meia_por }}"
                                        data-precos='{{ json_encode($produto->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) }}' data-bordas='{{ json_encode($produto->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>
                                        <td>
                                            @if(!empty($produto->imagem_url))
                                            <img class="produto-thumbnail" src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="Imagem do produto {{ $produto->nome }}" loading="lazy" decoding="async">
                                            @else
                                            <span style="color:#aaa;">Sem imagem</span>
                                            @endif
                                        </td>
                                        <td class="nome-cell">{{ $produto->nome }}</td>
                                        <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>


                                        <td>{{ $produto->categoria->nome ?? '-' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="add-produto-form">
                                                    <div class="qtd-wrapper">
                                                        <label for="qtd_{{ $produto->id }}" class="qtd-label">Quantidade</label>
                                                        <input
                                                            id="qtd_{{ $produto->id }}"
                                                            type="number"
                                                            class="qtd-input"
                                                            data-garcom-qtd
                                                            min="1"
                                                            max="99"
                                                            value="1"
                                                            required>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        class="btn-add garcom-add-list-btn"
                                                        data-garcom-add
                                                        aria-label="Adicionar {{ $produto->nome }} a lista do pedido">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                                                        </svg>
                                                        Adicionar a lista
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <dialog class="garcom-observacao-modal" id="garcomObservacaoModal" aria-labelledby="garcomObservacaoTitulo">
            <form method="dialog" class="garcom-observacao-modal__content">
                <h2 id="garcomObservacaoTitulo">Alguma observação?</h2>
                <p id="garcomObservacaoProduto"></p>
                <div id="garcomPizzaOptions" hidden>
                    <label for="garcomPizzaModo">Como deseja a pizza?</label>
                    <select id="garcomPizzaModo">
                        <option value="inteira">Pizza inteira, um sabor</option>
                        <option value="meia_a_meia">Meia a meia, combine dois sabores</option>
                    </select>
                    <div id="garcomPizzaSecondField" hidden>
                        <label for="garcomPizzaSabor2">Segundo sabor</label>
                        <select id="garcomPizzaSabor2">
                            <option value="">Selecione o segundo sabor</option>
                            @foreach($pizzas as $pizzaOpcao)
                                <option value="{{ $pizzaOpcao->id }}" data-precos='{{ json_encode($pizzaOpcao->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) }}' data-preco="{{ (float) $pizzaOpcao->preco }}" data-bordas='{{ json_encode($pizzaOpcao->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>{{ $pizzaOpcao->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="garcomPizzaBordaField">
                        <label for="garcomPizzaBorda">Qual borda deseja?</label>
                        <select id="garcomPizzaBorda" required>
                            <option value="">Escolha uma opção</option>
                            <option value="0">Sem borda</option>
                        </select>
                    </div>
                    <div id="garcomPizzaSizeField" hidden>
                        <label for="garcomPizzaTamanho">Tamanho e preço</label>
                        <select id="garcomPizzaTamanho">
                            <option value="">Selecione o tamanho</option>
                        </select>
                    </div>
                    <small id="garcomPizzaSizeUnavailable" hidden>Os sabores escolhidos não têm um tamanho em comum cadastrado.</small>
                </div>
                <div id="garcomPortionOptions" hidden>
                    <label for="garcomTipoPorcao">Porção</label>
                    <select id="garcomTipoPorcao">
                        <option value="inteira">Porção inteira</option>
                        <option value="meia">Meia porção</option>
                    </select>
                </div>
                <small id="garcomPersonalizationPrice"></small>
                <label for="garcomObservacaoInput">Informe retiradas ou acréscimos (opcional)</label>
                <textarea
                    id="garcomObservacaoInput"
                    maxlength="500"
                    rows="4"
                    placeholder="Ex.: sem cebola, acrescentar queijo..."></textarea>
                <div class="garcom-observacao-modal__actions">
                    <button type="button" class="garcom-order-clear" id="garcomObservacaoSem">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-add" id="garcomObservacaoConfirmar">Adicionar ao pedido</button>
                </div>
            </form>
        </dialog>
        @include('components.flash-toast')
        <script src="{{ asset('js/garcom.js') }}?v={{ filemtime(public_path('js/garcom.js')) }}"></script>
</body>

</html>

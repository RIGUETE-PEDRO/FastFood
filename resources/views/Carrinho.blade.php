<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carrinho</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Carrinho.css') }}">
</head>

<body>

    <main>
        <div class="voltar-link">
            <a href="{{ route('index') }}">voltar</a>
        </div>
        <div class="table-corpo">
            <h1>Carrinho</h1>

            <table class="table">
                @if ($carrinho->isEmpty())
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="64"
                        height="64"
                        fill="currentColor"
                        viewBox="0 0 16 16">
                        <path d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4H3a1 1 0 0 0-1 1v8.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V5a1 1 0 0 0-1-1h-2.5v-.5A2.5 2.5 0 0 0 8 1zm-1.5 3v-.5a1.5 1.5 0 0 1 3 0V4h-3z" />
                    </svg>

                    <h3>Nenhum produto encontrado</h3>
                    <p>Adicione produtos para começar a gerenciar seu carrinho.</p>
                </div>


                @else
                <thead>
                    <tr class="title-table">
                        <th>Adicionar</th>
                        <th>Imagem</th>
                        <th>Produto</th>
                        <th>Preço unitário</th>
                        <th>Preço total</th>
                        <th>Quantidade</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($carrinho as $item)
                    <tr>
                        <td>
                            <form action="{{ route('carrinho.toggle', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label class="cbx-container">
                                    <input
                                        type="checkbox"
                                        class="cbx"
                                        name="ativo"
                                        value="1"
                                        {{ $item->selecionado ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <span class="cbx-custom"></span>
                                </label>
                            </form>
                        </td>
                        <td><img src="{{ asset('img/produtos/' . $item->produto->imagem_url) }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px;"></td>
                        <td >{{ $item->produto->nome }}</td>
                        <td>R${{ $item->produto->preco }}</td>
                        <td>R${{ $item->preco_total }}</td>
                        <td>
                            <!-- Formulário para atualizar a quantidade -->
                            <form data-qty-form action="{{ route('carrinho.atualizarQuantidade', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" name="acao" value="menos" class="button negativo">−</button>

                                <input class="input-quantidade"
                                    type="number"
                                    name="quantidade"
                                    min="1"
                                    value="{{ $item->quantidade }}" />
                                <button type="submit" name="acao" value="mais" class="button positivo">+</button>
                            </form>
                        </td>


                        <td>
                            <form method="POST" action="{{ route('carrinho.remover', $item->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">Remover</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </table>
            <div class="finalizar-compra">

                <button id="btnFinalizarCompra" type="button" class="btn btn-primary">Finalizar Compra</button>
                <span class="total-compra">
                    Total: R$ {{ $carrinho->where('selecionado', true)->sum('preco_total') }}
                </span>

            </div>
        </div>

        <!-- Modal 1: escolher tipo -->
        <div id="finalizarModal" class="ff-modal" aria-hidden="true">
            <div class="ff-modal__overlay" aria-hidden="true"></div>
            <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="finalizarModalTitle">
                <div class="ff-modal__header">
                    <h2 id="finalizarModalTitle">Como você quer receber?</h2>
                    <button type="button" class="ff-modal__close" data-modal-close aria-label="Fechar">×</button>
                </div>

                <form id="tipoEntregaForm" method="POST" action="#">
                    @csrf

                    <p class="ff-modal__hint">Escolha uma opção para continuar.</p>

                    <div class="ff-choice">
                        <label class="ff-choice__item">
                            <input type="radio" name="tipo_entrega" value="retirar" checked>
                            <span>
                                <strong>Retirar no local</strong>
                                <small>Você vai informar o número da mesa no próximo passo.</small>
                            </span>
                        </label>

                        <label class="ff-choice__item">
                            <input type="radio" name="tipo_entrega" value="entrega">
                            <span>
                                <strong>Entrega</strong>
                                <small>Você vai informar o endereço no próximo passo.</small>
                            </span>
                        </label>
                    </div>

                    <div id="tipoEntregaErro" class="ff-modal__error" aria-live="polite"></div>

                    <div class="ff-modal__footer">
                        <button type="button" class="ff-btn ff-btn--ghost" data-modal-close>Cancelar</button>
                        <button type="submit" class="ff-btn ff-btn--primary">Continuar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2A: mesa -->
        <div id="mesaModal" class="ff-modal" aria-hidden="true">
            <div class="ff-modal__overlay" aria-hidden="true"></div>
            <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="mesaModalTitle">
                <div class="ff-modal__header">
                    <h2 id="mesaModalTitle">Informe a mesa</h2>
                    <button type="button" class="ff-modal__close" data-modal-close aria-label="Fechar">×</button>
                </div>

                <form id="mesaForm" method="POST" action="#">
                    @csrf
                    <input type="hidden" name="tipo_entrega" value="retirar">

                    <div class="ff-field">
                        <label for="mesa">Número da mesa</label>
                        <input id="mesa" name="mesa" type="text" inputmode="numeric" placeholder="Ex: 12" required>
                    </div>

                    <div id="mesaErro" class="ff-modal__error" aria-live="polite"></div>

                    <div class="ff-modal__footer">
                        <button type="button" class="ff-btn ff-btn--ghost" data-modal-back>Voltar</button>
                        <button type="submit" class="ff-btn ff-btn--primary">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2B: endereço -->
        <div id="enderecoModal" class="ff-modal" aria-hidden="true">
            <div class="ff-modal__overlay" aria-hidden="true"></div>
            <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="enderecoModalTitle">
                <div class="ff-modal__header">
                    <h2 id="enderecoModalTitle">Informe o endereço de entrega</h2>
                    <button type="button" class="ff-modal__close" data-modal-close aria-label="Fechar">×</button>
                </div>

                <form id="enderecoForm" method="POST" action="#">
                    @csrf
                    <input type="hidden" name="tipo_entrega" value="entrega">

                    <div class="ff-field">
                        <label for="endereco">Endereço</label>
                        <input id="endereco" name="endereco" type="text" placeholder="Rua, número, bairro..." required>
                    </div>

                    <div id="enderecoErro" class="ff-modal__error" aria-live="polite"></div>

                    <div class="ff-modal__footer">
                        <button type="button" class="ff-btn ff-btn--ghost" data-modal-back>Voltar</button>
                        <button type="submit" class="ff-btn ff-btn--primary">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>

</html>

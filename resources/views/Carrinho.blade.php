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

@php
$enderecos = collect($enderecos ?? []);
$cidadesLista = collect($cidades ?? []);
$cidadeSelecionada = old('cidade_id', session('checkout.cidade_id'));
$defaultEnderecoId = $enderecos->first()->id ?? null;
$enderecoSelecionado = old('endereco_opcao', $enderecoSelecionadoId ?? $defaultEnderecoId);
$modalReabrir = session('checkout.modal');
$totalEnderecos = $enderecos->count();
$podeRemoverEndereco = $totalEnderecos > 1;
$pagamentoSalvo = session('checkout.pagamento', []);
$pagamentoMetodoSelecionado = old('pagamento_metodo', $pagamentoSalvo['metodo'] ?? 'cartao_credito');
$pagamentoObservacoes = old('observacoes_pagamento', $pagamentoSalvo['observacoes'] ?? '');
@endphp

<body @if ($modalReabrir) data-open-modal="{{ $modalReabrir }}" @endif>

    <main>
        <div class="voltar-link">
            <a href="{{ route('index') }}">voltar</a>
        </div>

        <div class="table-corpo">
            <h1>Carrinho</h1>

            @if (session('success'))
            <div class="carrinho-alert carrinho-alert--success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
            <div class="carrinho-alert carrinho-alert--error">{{ session('error') }}</div>
            @endif

            @if ($carrinho->isEmpty())
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4H3a1 1 0 0 0-1 1v8.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V5a1 1 0 0 0-1-1h-2.5v-.5A2.5 2.5 0 0 0 8 1zm-1.5 3v-.5a1.5 1.5 0 0 1 3 0V4h-3z" />
                </svg>
                <h3>Nenhum produto encontrado</h3>
                <p>Adicione produtos para começar a gerenciar seu carrinho.</p>
            </div>
            @else
            <table class="table">
                <thead>
                    <tr class="title-table">
                        <th>Selecionar</th>
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
                                    <input type="checkbox" class="cbx" name="ativo" value="1" {{ $item->selecionado ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="cbx-custom"></span>
                                </label>
                            </form>
                        </td>
                        <td>
                            <img src="{{ asset('img/produtos/' . $item->produto->imagem_url) }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px;" alt="{{ $item->produto->nome }}">
                        </td>
                        <td>{{ $item->produto->nome }}</td>
                        <td>R${{ $item->produto->preco }}</td>
                        <td>R${{ $item->preco_total }}</td>
                        <td>
                            <form data-qty-form action="{{ route('carrinho.atualizarQuantidade', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" name="acao" value="menos" class="button negativo">−</button>
                                <input class="input-quantidade" type="number" name="quantidade" min="1" value="{{ $item->quantidade }}" />
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
            </table>
            @endif

            <div class="finalizar-compra">
                @if ($carrinho->where('selecionado', true)->count() > 0)
                <button id="btnFinalizarCompra" type="button" class="btn btn-primary">Finalizar Compra</button>
                <span class="total-compra">
                    Total: R$ {{ $carrinho->where('selecionado', true)->sum('preco_total') }}
                </span>
                @else
                <button id="btnFinalizarCompra" type="button" class="btn btn-primary" disabled>Finalizar Compra</button>
                <span class="total-compra">
                    Total: R$ {{ $carrinho->where('selecionado', true)->sum('preco_total') }}
                    <span class="aviso">selecione um produto</span>
                </span>
                @endif
            </div>
        </div>
    </main>

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
                    <button type="button" class="ff-btn ff-btn--ghost" data-modal-back="finalizarModal">Voltar</button>
                    <button type="submit" class="ff-btn ff-btn--primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2B: endereço salvo -->
    <div id="enderecoModal" class="ff-modal" aria-hidden="true">
        <div class="ff-modal__overlay" aria-hidden="true"></div>
        <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="enderecoModalTitle">
            <div class="ff-modal__header">
                <h2 id="enderecoModalTitle">Escolha um endereço de entrega</h2>
                <button type="button" class="ff-modal__close" data-modal-close aria-label="Fechar">×</button>
            </div>

            <form id="enderecoForm" method="POST" action="{{ route('carrinho.endereco') }}">
                @csrf
                <input type="hidden" name="tipo_entrega" value="entrega">

                <div class="ff-address-options">
                    @if ($enderecos->isNotEmpty())
                    <p class="ff-address-options__title">Selecione um endereço salvo</p>
                    <div class="ff-address-list" data-endereco-lista>
                        @foreach ($enderecos as $endereco)
                        <div class="ff-address-list__item {{ $podeRemoverEndereco ? '' : 'ff-address-list__item--locked' }}">
                            <label class="ff-choice__item">
                                <input type="radio" name="endereco_opcao" value="{{ $endereco->id }}" {{ (string) $enderecoSelecionado === (string) $endereco->id ? 'checked' : '' }}>
                                <span>
                                    <strong>{{ $endereco->logradouro }}{{ $endereco->numero ? ', ' . $endereco->numero : '' }}</strong>
                                    <small>{{ $endereco->bairro }}</small>
                                    @if ($endereco->complemento)
                                    <small>{{ $endereco->complemento }}</small>
                                    @endif
                                    @if ($endereco->cidade)
                                    <small>{{ $endereco->cidade->nome }}</small>
                                    @endif
                                </span>
                            </label>

                            <div class="ff-address-delete">
                                <button type="submit"
                                    class="ff-btn ff-btn--danger"
                                    form="form-delete-endereco-{{ $endereco->id }}"
                                    @if (!$podeRemoverEndereco) disabled aria-disabled="true" @endif>
                                    Excluir
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if (!$podeRemoverEndereco)
                    <p class="ff-address-hint" role="note">Mantenha pelo menos um endereço cadastrado para continuar usando o delivery.</p>
                    @endif
                    @else
                    <p class="ff-modal__hint">Você ainda não cadastrou nenhum endereço.</p>
                    @endif
                </div>

                <div id="enderecoSelecionadoErro" class="ff-modal__error" aria-live="polite"></div>

                <div class="ff-modal__footer ff-modal__footer--stack">
                    <button type="button" class="ff-btn ff-btn--ghost" data-modal-back="finalizarModal">Voltar</button>
                    <button type="button" class="ff-btn ff-btn--ghost" data-modal-open="enderecoNovoModal">Cadastrar novo endereço</button>
                    <button type="submit" class="ff-btn ff-btn--primary">Usar endereço selecionado</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2C: novo endereço -->
    <div id="enderecoNovoModal" class="ff-modal" aria-hidden="true">
        <div class="ff-modal__overlay" aria-hidden="true"></div>
        <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="enderecoNovoModalTitle">
            <div class="ff-modal__header">
                <h2 id="enderecoNovoModalTitle">Cadastrar novo endereço</h2>
                <button type="button" class="ff-modal__close" data-modal-close aria-label="Fechar">×</button>
            </div>

            <form id="enderecoNovoForm" method="POST" action="{{ route('carrinho.endereco') }}">
                @csrf
                <input type="hidden" name="tipo_entrega" value="entrega">
                <input type="hidden" name="endereco_opcao" value="novo">

                <div class="ff-field">
                    <label for="novo_bairro">Bairro</label>
                    <input id="novo_bairro" name="bairro" type="text" placeholder="Ex: Centro" value="{{ old('bairro') }}">
                </div>
                <div class="ff-field">
                    <label for="novo_rua">Rua</label>
                    <input id="novo_rua" name="rua" type="text" placeholder="Ex: Rua das Flores" value="{{ old('rua') }}">
                </div>
                <div class="ff-field">
                    <label for="novo_numero">Número</label>
                    <input id="novo_numero" name="numero" type="text" placeholder="Ex: 123" value="{{ old('numero') }}">
                </div>
                <div class="ff-field">
                    <label for="novo_complemento">Complemento</label>
                    <input id="novo_complemento" name="complemento" type="text" placeholder="Ex: Apt 45, Casa..." value="{{ old('complemento') }}">
                </div>

                <div class="ff-field">
                    <label for="novo_cidade">Cidade</label>
                    <select id="novo_cidade" name="cidade_id" class="ff-select">
                        <option value="">Selecione a cidade</option>
                        @foreach ($cidadesLista as $cidade)
                        <option value="{{ $cidade->id }}" {{ (string) $cidadeSelecionada === (string) $cidade->id ? 'selected' : '' }}>
                            {{ $cidade->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>


                <div id="enderecoNovoErro" class="ff-modal__error" aria-live="polite"></div>

                <div class="ff-modal__footer">
                    <button type="button" class="ff-btn ff-btn--ghost" data-modal-back="enderecoModal">Voltar</button>
                    <button type="submit" class="ff-btn ff-btn--primary">Cadastrar endereço</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: forma de pagamento -->
    <div id="pagamentoModal" class="ff-modal" aria-hidden="true">
        <div class="ff-modal__overlay" aria-hidden="true"></div>
        <div class="ff-modal__card" role="dialog" aria-modal="true" aria-labelledby="pagamentoModalTitle">
            <div class="ff-modal__header">
                <h2 id="pagamentoModalTitle">Como você quer pagar?</h2>
                <button type="button" class="ff-modal__close" data-modal-close aria-label="Fechar">×</button>
            </div>

            <form id="pagamentoForm" method="POST" action="{{ route('carrinho.pagamento') }}">
                @csrf

                <p class="ff-modal__hint">Selecione a forma de pagamento para seguir com o pedido.</p>

                <div class="ff-choice" style="margin-bottom: 12px;">
                    <label class="ff-choice__item">
                        <input type="radio" name="pagamento_metodo" value="cartao_credito" {{ $pagamentoMetodoSelecionado === 'cartao_credito' ? 'checked' : '' }}>
                        <span>
                            <strong>Cartão de crédito</strong>
                            <small>Levar máquina até você.</small>
                        </span>
                    </label>

                    <label class="ff-choice__item">
                        <input type="radio" name="pagamento_metodo" value="cartao_debito" {{ $pagamentoMetodoSelecionado === 'cartao_debito' ? 'checked' : '' }}>
                        <span>
                            <strong>Cartão de débito</strong>
                            <small>Pagamento no momento da entrega.</small>
                        </span>
                    </label>

                    <label class="ff-choice__item">
                        <input type="radio" name="pagamento_metodo" value="pix" {{ $pagamentoMetodoSelecionado === 'pix' ? 'checked' : '' }}>
                        <span>
                            <strong>Pix</strong>
                            <small>Enviamos a chave finalizando o pedido.</small>
                        </span>
                    </label>

                    <label class="ff-choice__item">
                        <input type="radio" name="pagamento_metodo" value="dinheiro" {{ $pagamentoMetodoSelecionado === 'dinheiro' ? 'checked' : '' }}>
                        <span>
                            <strong>Dinheiro</strong>
                            <small>Informe se precisa de troco.</small>
                        </span>
                    </label>
                </div>

                <div class="ff-field">
                    <label for="pagamento_observacoes">Observações (opcional)</label>
                    <textarea id="pagamento_observacoes" name="observacoes_pagamento" rows="3" placeholder="Ex: levar troco para R$ 50">{{ $pagamentoObservacoes }}</textarea>
                </div>

                <div class="ff-modal__footer ff-modal__footer--stack">
                    <button type="button" class="ff-btn ff-btn--ghost" data-modal-back="enderecoModal">Voltar</button>
                    <button type="submit" class="ff-btn ff-btn--primary">Confirmar pagamento</button>
                </div>
            </form>
        </div>
    </div>
    @foreach ($enderecos as $endereco)
    <form id="form-delete-endereco-{{ $endereco->id }}" class="ff-address-delete__form" action="{{ route('endereco.excluir', $endereco->id) }}" method="POST">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
</body>

</html>

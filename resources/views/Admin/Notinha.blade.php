<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <title>Cupom #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Notinha.css') }}">
</head>

<body>
    @php
        $subtotal = (float) $pedido->itens->sum(fn ($item) => $item->quantidade * $item->preco_unitario);
        $temEnderecoEntrega = filled(optional($pedido->endereco)->logradouro);
        $numeroMesa = optional($pedido->mesa)->numero_da_mesa;
        $horarioFuncionamento = $dadosEmpresa['Horario de Funcionamento'] ?? null;
    @endphp

    <div class="receipt-page">
        <div class="receipt-toolbar" aria-label="Acoes do cupom">
            <div class="receipt-toolbar__content">
                <div>
                    <span class="receipt-eyebrow">Cupom do pedido</span>
                    <h1>#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</h1>
                </div>

                <div class="receipt-actions">
                    <button type="button" class="receipt-button receipt-button--ghost" onclick="history.back()">Voltar</button>
                    <button type="button" class="receipt-button" onclick="window.print()">Imprimir</button>
                </div>
            </div>
        </div>

        <main class="cupom-container" aria-label="Cupom do pedido">
            <span class="receipt-preview-label" aria-hidden="true">Pre-visualizacao de impressao</span>

            <section class="cupom">
                <header class="receipt-header">
                    <strong class="receipt-store">{{ $dadosEmpresa['Nome da Empresa'] ?? 'FlashFood' }}</strong>
                    @if(!empty($dadosEmpresa['Msg_comanda']))
                        <span>{{ $dadosEmpresa['Msg_comanda'] }}</span>
                    @endif
                    <span>{{ $dadosEmpresa['Rua'] ?? '' }}, {{ $dadosEmpresa['Numero'] ?? 's/n' }}</span>
                    <span>{{ $dadosEmpresa['Bairro'] ?? '' }} - {{ $dadosEmpresa['Cidade'] ?? '' }}/{{ $dadosEmpresa['Estado'] ?? '' }}</span>
                    <span>CEP: {{ $dadosEmpresa['CEP'] ?? 'Nao informado' }}</span>
                    <span>Tel: {{ $dadosEmpresa['Telefone'] ?? 'Nao informado' }}</span>
                    <span>CNPJ: {{ $dadosEmpresa['CNPJ'] ?? 'Nao informado' }}</span>
                </header>

                <div class="receipt-separator"></div>

                <section class="receipt-block">
                    <div class="receipt-row">
                        <span>Cupom</span>
                        <strong>#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</strong>
                    </div>
                    <div class="receipt-row">
                        <span>Data</span>
                        <span>{{ optional($pedido->created_at)->format('d/m/Y') ?? 'N/D' }}</span>
                    </div>
                    <div class="receipt-row">
                        <span>Hora</span>
                        <span>{{ optional($pedido->created_at)->format('H:i') ?? 'N/D' }}</span>
                    </div>
                    <div class="receipt-row">
                        <span>Cliente</span>
                        <strong>{{ optional($pedido->usuario)->nome ?? 'Nao informado' }}</strong>
                    </div>
                    <div class="receipt-row">
                        <span>Tipo</span>
                        <strong class="receipt-pill">{{ $temEnderecoEntrega ? 'Entrega' : 'Retirada' }}</strong>
                    </div>
                    @unless($temEnderecoEntrega)
                        <div class="receipt-row">
                            <span>Mesa</span>
                            <span>{{ $numeroMesa ? 'Mesa ' . $numeroMesa : 'Balcao' }}</span>
                        </div>
                    @endunless
                </section>

                @if($temEnderecoEntrega)
                    <div class="receipt-separator"></div>

                    <section class="receipt-block">
                        <h2 class="receipt-title">Endereco de entrega</h2>
                        <p>{{ $pedido->endereco->logradouro }}, {{ $pedido->endereco->numero ?? 's/n' }}</p>
                        <p>{{ $pedido->endereco->bairro ?? 'Bairro nao informado' }}</p>
                        @if(!empty($pedido->endereco->complemento))
                            <p>Compl.: {{ $pedido->endereco->complemento }}</p>
                        @endif
                        <p>{{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Cidade nao informada' }}</p>
                    </section>
                @endif

                <div class="receipt-separator"></div>

                <section class="receipt-block">
                    <h2 class="receipt-title">Itens do pedido</h2>
                    @forelse($pedido->itens as $item)
                        @php
                            $totalItem = (float) $item->quantidade * (float) $item->preco_unitario;
                        @endphp
                        <div class="receipt-item">
                            <div class="receipt-item__line">
                                <strong>{{ $item->quantidade }}x {{ optional($item->produto)->nome ?? 'Produto removido' }}</strong>
                            </div>
                            <div class="receipt-row receipt-row--small">
                                <span>Unit. R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
                                <span>R$ {{ number_format($totalItem, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="receipt-muted">Nenhum item neste pedido.</p>
                    @endforelse
                </section>

                <div class="receipt-separator"></div>

                <section class="receipt-block">
                    <div class="receipt-row">
                        <span>Qtd. itens</span>
                        <span>{{ $pedido->itens->sum('quantidade') }}</span>
                    </div>
                    <div class="receipt-row">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="receipt-row">
                        <span>Pagamento</span>
                        <span>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Nao informado' }}</span>
                    </div>
                    <div class="receipt-total">
                        <span>Total</span>
                        <strong>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</strong>
                    </div>
                </section>

                <div class="receipt-separator"></div>

                <footer class="receipt-footer">
                    <strong>Obrigado pela preferencia!</strong>
                    <span>Este cupom nao tem validade fiscal.</span>
                    <span>Guarde para eventuais conferencias.</span>
                    @if(!empty($horarioFuncionamento))
                        <span>Atendimento: {{ $horarioFuncionamento }}</span>
                    @elseif(!empty($dadosEmpresa['Horário de Funcionamento']))
                        <span>Atendimento: {{ $dadosEmpresa['Horário de Funcionamento'] }}</span>
                    @endif
                </footer>
            </section>
        </main>
    </div>
</body>

</html>

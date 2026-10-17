<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    @include('partials.favicon')
    <title>Cupom #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Notinha.css') }}?v={{ filemtime(public_path('css/Admin/Notinha.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/PersonalizacaoProduto.css') }}">
</head>

<body data-print-settings='@json($configuracoesImpressao ?? [])' data-auto-print="{{ !empty($imprimirAutomaticamente) ? '1' : '0' }}">
    @php
        $subtotal = (float) $pedido->itens->sum(fn ($item) => $item->quantidade * $item->preco_unitario);
        $temEnderecoEntrega = filled(optional($pedido->endereco)->logradouro);
        $numeroMesa = optional($pedido->mesa)->numero_da_mesa;
        $horarioFuncionamento = $dadosEmpresa['Horario de Funcionamento'] ?? null;
        $fontesImpressao = [
            'monospace' => '"Courier New", monospace',
            'arial' => 'Arial, sans-serif',
            'georgia' => 'Georgia, serif',
        ];
        $fonteImpressao = $fontesImpressao[$configuracoesImpressao['receiptFont'] ?? ''] ?? '"Courier New", monospace';
        $tamanhosImpressao = ['10' => '10px', '12' => '12px', '14' => '14px'];
        $tamanhoImpressao = $tamanhosImpressao[(string) ($configuracoesImpressao['fontSize'] ?? '')] ?? '12px';
    @endphp

    <div class="receipt-page">
        <div class="receipt-toolbar" aria-label="Ações do cupom">
            <div class="receipt-toolbar__content">
                <div>
                    <span class="receipt-eyebrow">Cupom do pedido</span>
                    <h1>#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</h1>
                </div>

                <div class="receipt-actions">
                    <button type="button" class="receipt-button receipt-button--ghost" onclick="history.back()">Voltar</button>
                    <button type="button" class="receipt-button" data-print-receipt>Imprimir</button>
                </div>
            </div>
        </div>

        <main class="cupom-container" aria-label="Cupom do pedido">
            <span class="receipt-preview-label" aria-hidden="true">Pré-visualização de impressão</span>

            <section class="cupom" style="font-family: {{ $fonteImpressao }} !important; font-size: {{ $tamanhoImpressao }} !important;">
                <header class="receipt-header">
                    <strong class="receipt-store">{{ $dadosEmpresa['Nome da Empresa'] ?? 'FlashFood' }}</strong>
                    @if(!empty($dadosEmpresa['Msg_comanda']))
                        <span>{{ $dadosEmpresa['Msg_comanda'] }}</span>
                    @endif
                    <span>{{ $dadosEmpresa['Rua'] ?? '' }}, {{ $dadosEmpresa['Numero'] ?? 's/n' }}</span>
                    <span>{{ $dadosEmpresa['Bairro'] ?? '' }} - {{ $dadosEmpresa['Cidade'] ?? '' }}/{{ $dadosEmpresa['Estado'] ?? '' }}</span>
                    <span>CEP: {{ $dadosEmpresa['CEP'] ?? 'Não informado' }}</span>
                    <span>Tel: {{ $dadosEmpresa['Telefone'] ?? 'Não informado' }}</span>
                    <span>CNPJ: {{ $dadosEmpresa['CNPJ'] ?? 'Não informado' }}</span>
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
                        <strong>{{ optional($pedido->usuario)->nome ?? 'Não informado' }}</strong>
                    </div>
                    <div class="receipt-row">
                        <span>Tipo</span>
                        <strong class="receipt-pill">{{ $temEnderecoEntrega ? 'Entrega' : 'Mesa' }}</strong>
                    </div>
                    @unless($temEnderecoEntrega)
                        <div class="receipt-row">
                            <span>Mesa</span>
                            <span>{{ $numeroMesa ? 'Mesa ' . $numeroMesa : 'Balcão' }}</span>
                        </div>
                    @endunless
                </section>

                @if($temEnderecoEntrega)
                    <div class="receipt-separator"></div>

                    <section class="receipt-block">
                        <h2 class="receipt-title">Endereço de entrega</h2>
                        <p>{{ $pedido->endereco->logradouro }}, {{ $pedido->endereco->numero ?? 's/n' }}</p>
                        <p>{{ $pedido->endereco->bairro ?? 'Bairro não informado' }}</p>
                        @if(!empty($pedido->endereco->complemento))
                            <p>Compl.: {{ $pedido->endereco->complemento }}</p>
                        @endif
                        <p>{{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Cidade não informada' }}</p>
                    </section>
                @endif

                <div class="receipt-separator"></div>

                <section class="receipt-block">
                    <h2 class="receipt-title">Itens do pedido</h2>
                    @forelse($pedido->itens as $item)
                        @php
                            $totalItem = (float) $item->quantidade * (float) $item->preco_unitario;
                            $configuracaoItem = $item->personalizacao ?? [];
                            $tituloItem = ($configuracaoItem['tipo'] ?? null) === 'pizza'
                                ? 'Pizza' . (filled($configuracaoItem['tamanho'] ?? null) ? ' ' . $configuracaoItem['tamanho'] : '')
                                : (optional($item->produto)->nome ?? 'Produto removido');
                        @endphp
                        <div class="receipt-item">
                            <div class="receipt-item__line">
                                <strong>{{ $item->quantidade }}x {{ $tituloItem }}</strong>
                                @include('partials.detalhes-personalizacao-item', ['personalizacao' => $item->personalizacao])
                                @if(filled($item->observacao))
                                    <small>Obs.: {{ $item->observacao }}</small>
                                @endif
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
                        <span>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Não informado' }}</span>
                    </div>
                    <div class="receipt-total">
                        <span>Total</span>
                        <strong>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</strong>
                    </div>
                </section>

                <div class="receipt-separator"></div>

                <footer class="receipt-footer">
                    <strong>Obrigado pela preferencia!</strong>
                    <span>Este cupom não tem validade fiscal.</span>
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
    <script src="{{ asset('js/admin-print-preferences.js') }}?v={{ filemtime(public_path('js/admin-print-preferences.js')) }}" defer></script>
</body>

</html>

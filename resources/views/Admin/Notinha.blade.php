<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notinha</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Keyclock.css') }}">
</head>
<link rel="stylesheet" href="{{ asset('../css/Admin/Notinha.css') }}">
<body>

<div class="cupom-container">
    <div class="cupom">

        <!-- Cabeçalho com Logo -->
        <div class="header">
            <div class="logo"></div>
            <div class="nome-lanchonete">{{$dadosEmpresa['Nome da Empresa']}}</div>
            <div class="slogan">{{$dadosEmpresa['Msg_comanda']}}</div>
        </div>

        <!-- Endereço da Lanchonete -->
        <div class="endereco-box">
            <div class="endereco-titulo">📍 Nosso Endereço</div>
            <div class="endereco-texto">{{$dadosEmpresa['Rua']}}, nº {{$dadosEmpresa['Numero']}}</div>
            <div class="endereco-texto">{{$dadosEmpresa['Bairro']}} - {{$dadosEmpresa['Cidade']}}\{{$dadosEmpresa['Estado']}}</div>
            <div class="endereco-texto">CEP: {{$dadosEmpresa['CEP']}}</div>
            <div class="telefone">☎ {{$dadosEmpresa['Telefone']}}</div>
        </div>

        <!-- Data e Hora -->
        <div class="data-hora-box">
            <div class="data-hora-item">
                <div class="data-hora-label">📅 Data</div>
                <div class="data-hora-valor">{{ $pedido->created_at->format('d/m/Y') }}</div>
            </div>
            <div class="data-hora-item">
                <div class="data-hora-label">🕐 Horário</div>
                <div class="data-hora-valor">{{ $pedido->created_at->format('H:i:s') }}</div>
            </div>
        </div>

        <!-- Título Pedido -->
        <div class="section-title">Seu Pedido</div>

        <!-- Lista de Itens -->
        <div class="itens-lista">
            @forelse($pedido->itens as $index => $item)
                <div class="item">
                    <div class="item-header">
                        <div class="item-numero">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="item-nome">{{ optional($item->produto)->nome ?? 'Produto removido' }}</div>
                        <div class="item-preco">R$ {{ number_format((float) $item->quantidade * (float) $item->preco_unitario, 2, ',', '.') }}</div>
                    </div>
                    <div class="item-detalhes">
                        <div class="item-qtd-unit">
                            <span>Quantidade: {{ $item->quantidade }} un</span>
                            <span>Unitário: R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="item">
                    <p style="text-align: center; color: #999;">Nenhum item neste pedido</p>
                </div>
            @endforelse
        </div>

        <!-- Totais -->
        <div class="totais-container">
            <div class="total-linha">
                <span>Quantidade de Itens:</span>
                <span><strong>{{ $pedido->itens->sum('quantidade') }}</strong></span>
            </div>
            <div class="total-linha subtotal">
                <span>Subtotal:</span>
                <span>R$ {{ number_format((float) $pedido->itens->sum(function($item) { return $item->quantidade * $item->preco_unitario; }), 2, ',', '.') }}</span>
            </div>
            <div class="total-linha">
                <span>Forma de Pagamento:</span>
                <span>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'N/A' }}</span>
            </div>
            <div class="total-linha">
                <span>Desconto:</span>
                <span>- R$ 0,00</span>
            </div>

            <div class="total-final">
                <span>VALOR TOTAL</span>
                <span>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Forma de Pagamento -->
        <div class="pagamento-box">
            <div class="pagamento-titulo">💳 Forma de Pagamento</div>

            <div class="pagamento-item">
                <span>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Dinheiro' }}</span>
                <span>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Informação Adicional -->
        <div class="info-adicional">
            <strong>ℹ️ Informações Importantes</strong>
            {{ $dadosEmpresa['CNPJ'] ?? 'CNPJ não informado' }}<br>
            Cupom Nº: {{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }} | PDV: Caixa 01<br>
            Cliente: {{ optional($pedido->usuario)->nome ?? 'Desconhecido' }}
        </div>

        <!-- Rodapé -->
        <div class="footer">

            <div class="divider">
                <div class="divider-text">★ ★ ★</div>
            </div>

            <div class="agradecimento">
                Obrigado!
            </div>

            <div class="divider">
                <div class="divider-text">Volte Sempre</div>
            </div>

            <div class="redes-sociais">
                <strong>📱 Siga-nos nas Redes Sociais</strong>
                @burgerpointoficial<br>
                Facebook | Instagram | WhatsApp
            </div>

            <div class="info-adicional" style="margin-top: 15px;">
                <strong>🕐 Horário de Funcionamento</strong>
                {{$dadosEmpresa['Horário de Funcionamento'] ?? 'Não informado'}}
            </div>

            <div class="observacao-final">
                Este cupom não tem validade fiscal.<br>
                Guarde para eventuais reclamações ou trocas.<br>
                Delivery disponível! Peça pelo WhatsApp: {{$dadosEmpresa['Telefone']}}
            </div>

        </div>

    </div>
</div>
</body>
</html>

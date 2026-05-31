@php
    $statusTextoOriginal = (string) optional($pedido->statusRelacionamento)->status;
    $statusTexto = strtoupper(trim(\Illuminate\Support\Str::ascii($statusTextoOriginal)));
    $statusLabel = $statusTextoOriginal !== '' ? strtoupper($statusTextoOriginal) : 'STATUS INDEFINIDO';
    $statusClasse = match ($statusTexto) {
        'PENDENTE' => 'badge-status badge-status--pendente',
        'EM PREPARACAO', 'EM PREPARO', 'PREPARANDO' => 'badge-status badge-status--preparo',
        'A CAMINHO', 'EM TRANSITO', 'EM PERCURSO' => 'badge-status badge-status--expedicao',
        'ENTREGUE' => 'badge-status badge-status--entregue',
        'CANCELADO', 'RECUSADO' => 'badge-status badge-status--cancelado',
        default => 'badge-status badge-status--padrao',
    };
    $temEnderecoEntrega = filled(optional($pedido->endereco)->logradouro);
@endphp

<article class="pedido-card">
    <header class="pedido-card__header">
        <div>
            <h2 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h2>
            <span class="pedido-card__subtitulo">Realizado em {{ optional($pedido->created_at)->format('d/m/Y \a\s H:i') ?? 'N/D' }}</span>
        </div>
        <span class="{{ $statusClasse }}">{{ $statusLabel }}</span>
    </header>

    <section class="pedido-card__secao">
        <h3 class="pedido-card__secao-titulo">Resumo</h3>
        <dl class="pedido-dados">
            <div>
                <dt>Metodo de pagamento</dt>
                <dd>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Nao informado' }}</dd>
            </div>
            <div>
                <dt>Valor total</dt>
                <dd>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</dd>
            </div>
            <div>
                <dt>Tipo do pedido</dt>
                <dd>{{ $temEnderecoEntrega ? 'Entrega' : 'Retirada no local' }}</dd>
            </div>
        </dl>
    </section>

    @if($temEnderecoEntrega)
        <section class="pedido-card__secao">
            <h3 class="pedido-card__secao-titulo">Endereco de entrega</h3>
            <dl class="pedido-endereco">
                <div>
                    <dt>Logradouro</dt>
                    <dd>{{ optional($pedido->endereco)->logradouro ?? 'Nao informado' }}</dd>
                </div>
                <div>
                    <dt>Numero</dt>
                    <dd>{{ optional($pedido->endereco)->numero ?? 's/n' }}</dd>
                </div>
                <div>
                    <dt>Bairro</dt>
                    <dd>{{ optional($pedido->endereco)->bairro ?? 'Nao informado' }}</dd>
                </div>
                <div>
                    <dt>Complemento</dt>
                    <dd>{{ optional($pedido->endereco)->complemento ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Cidade</dt>
                    <dd>{{ optional(optional($pedido->endereco)->cidade)->nome ?? 'Nao informado' }}</dd>
                </div>
            </dl>
        </section>
    @endif

    @if($pedido->itens->isNotEmpty())
        <section class="pedido-card__secao">
            <h3 class="pedido-card__secao-titulo">Itens do pedido</h3>
            <ul class="pedido-itens">
                @foreach($pedido->itens as $item)
                    <li class="pedido-itens__linha">
                        <div>
                            <span class="pedido-itens__titulo">{{ optional($item->produto)->nome ?? 'Produto removido' }}</span>
                            <span class="pedido-itens__detalhe">{{ $item->quantidade }} x R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
                        </div>
                        <strong>R$ {{ number_format((float) $item->quantidade * (float) $item->preco_unitario, 2, ',', '.') }}</strong>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</article>

@php
    $statusAtual = $pedido->status_enum;
    $nextStatus = $pedido->next_status;
    $statusAtualValor = $statusAtual->value ?? 0;
    $statusClasses = [
        1 => 'badge-status badge-status--pendente',
        2 => 'badge-status badge-status--preparo',
        3 => 'badge-status badge-status--expedicao',
        4 => 'badge-status badge-status--entregue',
        5 => 'badge-status badge-status--cancelado',
    ];
    $classeStatus = $statusClasses[$statusAtualValor] ?? 'badge-status badge-status--padrao';
@endphp

<article class="pedido-card shadow-sm" data-status="{{ $statusAtualValor }}">
    <header class="pedido-card__header">
        <div>
            <h2 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h2>
            <p class="pedido-card__subtitulo mb-0">{{ optional($pedido->created_at)->format('d/m/Y \à\s H:i') ?? 'Data não informada' }}</p>
            <p class="pedido-card__cliente mb-0">Cliente: {{ optional($pedido->usuario)->nome ?? 'Desconhecido' }}</p>
        </div>
        <span class="{{ $classeStatus }}">{{ $pedido->status_label }}</span>
    </header>

    <section class="pedido-card__secao">
        <h3 class="pedido-card__secao-titulo">Linha do tempo</h3>
        <ol class="timeline-status">
            @foreach($statusTimeline as $statusItem)
                @php
                    $ativo = $statusItem['value'] === $statusAtualValor;
                    $concluido = $statusItem['value'] < $statusAtualValor;
                @endphp
                <li class="timeline-status__item {{ $ativo ? 'is-ativo' : '' }} {{ $concluido ? 'is-concluido' : '' }}">
                    <span class="timeline-status__etapa">{{ $statusItem['label'] }}</span>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="pedido-card__secao">
        <h3 class="pedido-card__secao-titulo">Resumo</h3>
        <dl class="pedido-dados">
            <div>
                <dt>Pagamento</dt>
                <dd>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Não informado' }}</dd>
            </div>
            <div>
                <dt>Total</dt>
                <dd>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</dd>
            </div>
            <div>
                <dt>Endereço</dt>
                <dd>
                    @if(optional($pedido->endereco)->logradouro)
                        {{ $pedido->endereco->logradouro }}, {{ $pedido->endereco->numero ?? 's/n' }} - {{ $pedido->endereco->bairro ?? '' }}<br>
                        {{ optional(optional($pedido->endereco)->cidade)->nome ?? '' }}
                    @else
                        Retirada no balcão
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    @if($pedido->itens->isNotEmpty())
        <section class="pedido-card__secao">
            <h3 class="pedido-card__secao-titulo">Itens</h3>
            <ul class="pedido-itens">
                @foreach($pedido->itens as $item)
                    <li class="pedido-itens__linha">
                        <div>
                            <span class="pedido-itens__titulo">{{ optional($item->produto)->nome ?? 'Produto removido' }}</span>
                            <span class="pedido-itens__detalhe">{{ $item->quantidade }} × R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
                        </div>
                        <strong>R$ {{ number_format((float) $item->quantidade * (float) $item->preco_unitario, 2, ',', '.') }}</strong>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @unless(!empty($desabilitarAcoes))
        <section class="pedido-card__secao pedido-card__secao--acoes">
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3">
                <form class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-3" method="POST" action="{{ route('Pedidos.StatusAtualizar', $pedido) }}">
                    @csrf
                    @method('PATCH')
                    <label class="form-label mb-0 me-sm-2" for="status-{{ $pedido->id }}">Atualizar status</label>
                    <select id="status-{{ $pedido->id }}" name="status" class="form-select">
                        @foreach($statusOptions as $option)
                            <option value="{{ $option['value'] }}" @selected($option['value'] === $statusAtualValor)>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>

                @if($nextStatus)
                    <form method="POST" action="{{ route('Pedidos.StatusAvancar', $pedido) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-success">
                            Avançar para {{ $statusLabels[$nextStatus->value] ?? 'próximo status' }}
                        </button>
                    </form>
                @endif
            </div>
        </section>
    @endunless
</article>

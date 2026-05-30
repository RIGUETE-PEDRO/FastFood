@php
    $statusAtual = $pedido->status_enum;
    $nextStatus = $pedido->next_status;
    $statusAtualValor = $statusAtual->value ?? 0;
    $colapsavel = (bool) ($colapsavel ?? false);
    $iniciarRecolhido = (bool) ($iniciarRecolhido ?? false);
    $statusClasses = [
        1 => 'badge-status badge-status--pendente',
        2 => 'badge-status badge-status--preparo',
        3 => 'badge-status badge-status--expedicao',
        4 => 'badge-status badge-status--entregue',
        5 => 'badge-status badge-status--cancelado',
    ];
    $classeStatus = $statusClasses[$statusAtualValor] ?? 'badge-status badge-status--padrao';
    $temEnderecoEntrega = filled(optional($pedido->endereco)->logradouro);
@endphp

<article
    class="pedido-card shadow-sm"
    data-status="{{ $statusAtualValor }}"
    data-cliente="{{ Str::lower(optional($pedido->usuario)->nome ?? 'desconhecido') }}"
    data-pedido-data="{{ optional($pedido->created_at)->format('Y-m-d') }}"
>
    @if($colapsavel)
        <details class="pedido-collapse" @if(!$iniciarRecolhido) open @endif>
            <summary class="pedido-collapse__summary">
                <header class="pedido-card__header">
                    <div>
                        <h2 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h2>
                        <p class="pedido-card__subtitulo mb-0">{{ optional($pedido->created_at)->format('d/m/Y \a\s H:i') ?? 'Data nao informada' }}</p>
                        <p class="pedido-card__cliente mb-0">Cliente: {{ optional($pedido->usuario)->nome ?? 'Desconhecido' }}</p>
                    </div>
                    <div class="pedido-card__header-right">
                        <span class="{{ $classeStatus }}">{{ $pedido->status_label }}</span>
                        <span class="pedido-collapse__chevron" aria-hidden="true">v</span>
                    </div>
                </header>
            </summary>

            <div class="pedido-collapse__content">
    @else
        <header class="pedido-card__header">
            <div>
                <h2 class="pedido-card__titulo">Pedido #{{ $pedido->id }}</h2>
                <p class="pedido-card__subtitulo mb-0">{{ optional($pedido->created_at)->format('d/m/Y \a\s H:i') ?? 'Data nao informada' }}</p>
                <p class="pedido-card__cliente mb-0">Cliente: {{ optional($pedido->usuario)->nome ?? 'Desconhecido' }}</p>
            </div>
            <div class="pedido-card__header-right">
                <span class="{{ $classeStatus }}">{{ $pedido->status_label }}</span>
            </div>
        </header>

        <div class="pedido-card__body">
    @endif
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
                    <dd>{{ optional($pedido->formaPagamento)->tipo_pagamento ?? 'Nao informado' }}</dd>
                </div>
                <div>
                    <dt>Total</dt>
                    <dd>R$ {{ number_format((float) $pedido->valor_total, 2, ',', '.') }}</dd>
                </div>
                <div>
                    <dt>{{ $temEnderecoEntrega ? 'Endereco' : 'Atendimento' }}</dt>
                    <dd>
                        @if($temEnderecoEntrega)
                            {{ $pedido->endereco->logradouro }}, {{ $pedido->endereco->numero ?? 's/n' }} - {{ $pedido->endereco->bairro ?? '' }}<br>
                            {{ optional(optional($pedido->endereco)->cidade)->nome ?? '' }}
                        @else
                            Retirada no local
                            @if(optional($pedido->mesa)->numero_da_mesa)
                                <br>Mesa {{ $pedido->mesa->numero_da_mesa }}
                            @endif
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
                                <span class="pedido-itens__detalhe">{{ $item->quantidade }} x R$ {{ number_format((float) $item->preco_unitario, 2, ',', '.') }}</span>
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
                        <form class="pedido-avancar-form" method="POST" action="{{ route('Pedidos.StatusAvancar', $pedido) }}" data-disable-on-submit>
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-avancar-status" data-avancar-button aria-label="Avancar status do pedido #{{ $pedido->id }}">
                                <span class="btn-avancar-status__text">
                                    Avancar para {{ $statusLabels[$nextStatus->value] ?? 'proximo status' }}
                                </span>
                                <span class="btn-avancar-status__loading" aria-hidden="true">Avancando...</span>
                                <span class="btn-avancar-status__icon" aria-hidden="true">-&gt;</span>
                            </button>
                        </form>
                    @endif

                    <form method="GET" action="{{ route('Pedidos.GerarCupom', $pedido) }}" class="m-0">
                        <button type="submit" class="btn btn-outline-secondary btn-geraCupom" aria-label="Gerar cupom do pedido #{{ $pedido->id }}">Gerar cupom</button>
                    </form>
                </div>
            </section>
        @endunless
        @if(!empty($desabilitarAcoes) && $statusAtualValor === 4)
            <section class="pedido-card__secao pedido-card__secao--acoes pedido-card__secao--cupom">
                <form method="GET" action="{{ route('Pedidos.GerarCupom', $pedido) }}" class="m-0 pedido-cupom-form">
                    <button type="submit" class="btn btn-outline-secondary btn-geraCupom" aria-label="Gerar cupom do pedido #{{ $pedido->id }}">Gerar cupom</button>
                </form>
            </section>
        @endif
    @if($colapsavel)
            </div>
        </details>
    @else
        </div>
    @endif
</article>

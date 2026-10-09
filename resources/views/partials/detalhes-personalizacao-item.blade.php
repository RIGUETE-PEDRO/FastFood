@php
    $detalhesPersonalizacao = is_array($personalizacao ?? null) ? $personalizacao : [];
    $sabor1 = data_get($detalhesPersonalizacao, 'sabor_1.nome');
    $sabor2 = data_get($detalhesPersonalizacao, 'sabor_2.nome');
@endphp

@if(($detalhesPersonalizacao['tipo'] ?? null) === 'pizza')
    @if(filled($detalhesPersonalizacao['tamanho'] ?? null))
        <span class="item-personalizacao__linha">Tamanho: {{ $detalhesPersonalizacao['tamanho'] }}</span>
    @endif
    @if(($detalhesPersonalizacao['modo'] ?? 'inteira') === 'meia_a_meia')
        @if(filled($sabor1))
            <span class="item-personalizacao__linha">1/2 {{ $sabor1 }}</span>
        @endif
        @if(filled($sabor2))
            <span class="item-personalizacao__linha">1/2 {{ $sabor2 }}</span>
        @endif
    @elseif(filled($sabor1))
        <span class="item-personalizacao__linha">Sabor: {{ $sabor1 }}</span>
    @endif
    @if(filled($detalhesPersonalizacao['borda'] ?? null))
        <span class="item-personalizacao__linha">Borda: {{ $detalhesPersonalizacao['borda'] }}</span>
    @endif
@elseif(($detalhesPersonalizacao['tipo'] ?? null) === 'porcao')
    <span class="item-personalizacao__linha">
        {{ ($detalhesPersonalizacao['tipo_porcao'] ?? 'inteira') === 'meia' ? 'Meia porção' : 'Porção inteira' }}
    </span>
@endif

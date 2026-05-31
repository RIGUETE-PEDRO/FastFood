<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>Historico de Mesas</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Mesa.css') }}">
</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')

        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span> Menu
            </button>

            <main>
                @php
                    $formaLabel = static function (?string $metodo): string {
                        return match ($metodo) {
                            'cartao' => 'Cartao',
                            'cartao_credito' => 'Cartao de credito',
                            'cartao_debito' => 'Cartao de debito',
                            'pix' => 'Pix',
                            'dinheiro' => 'Dinheiro',
                            'nao_informado' => 'Nao informado',
                            default => $metodo ? str_replace('_', ' ', ucfirst($metodo)) : 'Nao informado',
                        };
                    };
                @endphp

                <section class="mesas-page mesas-history-page">
                    <header class="mesas-header mesas-history-header">
                        <div>
                            <span class="mesa-detail-header__eyebrow">Historico operacional</span>
                            <h1 class="mesas-title">Mesas fechadas</h1>
                            <p class="mesas-subtitle">Consulte fechamentos de mesa e formas de pagamento utilizadas.</p>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('mesas.index') }}" class="btn topo btn-Historico">Voltar para mesas</a>
                        </div>
                    </header>

                    <section class="mesa-history-summary" aria-label="Resumo do historico">
                        <article>
                            <span>Fechamentos</span>
                            <strong>{{ $fechamentos->total() }}</strong>
                        </article>
                        <article>
                            <span>Total nesta pagina</span>
                            <strong>R$ {{ number_format((float) $fechamentos->getCollection()->sum('total_pago'), 2, ',', '.') }}</strong>
                        </article>
                    </section>

                    @if($fechamentos->isEmpty())
                        <div class="mesa-empty-state">
                            <strong>Nenhuma mesa fechada ainda</strong>
                            <span>Quando uma comanda for totalmente paga, o fechamento aparecera aqui.</span>
                        </div>
                    @else
                        <div class="mesa-history-list">
                            @foreach($fechamentos as $fechamento)
                                @php
                                    $formas = $fechamento->formas_pagamento ?? [];
                                    $resumos = $fechamento->pagamentos_resumo ?? [];
                                @endphp

                                <article class="mesa-history-card">
                                    <div class="mesa-history-card__main">
                                        <div>
                                            <span class="mesa-history-card__eyebrow">Mesa {{ $fechamento->numero_da_mesa }}</span>
                                            <h2>Fechamento #{{ $fechamento->id }}</h2>
                                            <p>{{ optional($fechamento->fechado_em)->format('d/m/Y H:i') ?? 'Data nao informada' }}</p>
                                        </div>
                                        <strong class="mesa-history-card__total">
                                            R$ {{ number_format((float) $fechamento->total_pago, 2, ',', '.') }}
                                        </strong>
                                    </div>

                                    <div class="mesa-history-card__meta">
                                        <span>{{ (int) $fechamento->total_itens }} {{ (int) $fechamento->total_itens === 1 ? 'item' : 'itens' }}</span>
                                        <div class="mesa-history-payments">
                                            @forelse($formas as $forma)
                                                <span>{{ $formaLabel($forma) }}</span>
                                            @empty
                                                <span>Nao informado</span>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="mesa-history-breakdown">
                                        @forelse($resumos as $resumo)
                                            <div>
                                                <span>{{ $formaLabel($resumo['metodo'] ?? null) }}</span>
                                                <strong>R$ {{ number_format((float) ($resumo['valor'] ?? 0), 2, ',', '.') }}</strong>
                                            </div>
                                        @empty
                                            <div>
                                                <span>Pagamento</span>
                                                <strong>Nao informado</strong>
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mesa-history-pagination">
                            {{ $fechamentos->links() }}
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</body>

</html>

@php
    $estatisticas = $estatisticas ?? [
        'total' => $auditorias->total(),
        'tipos_acao' => $auditorias->getCollection()->whereNotNull('acao')->pluck('acao')->unique()->count(),
        'ultima_acao' => $auditorias->first(),
    ];

    $ultimaAcao = $estatisticas['ultima_acao'] ?? null;
@endphp

<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title text-muted">Total de registros</h6>
            <h3 class="text-primary">{{ $estatisticas['total'] }}</h3>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title text-muted">
                <i class="fas fa-clock text-warning" aria-hidden="true"></i> Ultima acao
            </h6>
            @if($ultimaAcao)
                <p class="auditoria-timestamp mb-1">
                    {{ $ultimaAcao->created_at?->format('d/m/Y') }}
                </p>
                <small class="text-muted">
                    {{ $ultimaAcao->created_at?->format('H:i:s') }}
                </small>
            @else
                <p class="text-secondary mb-0">Nenhum registro</p>
            @endif
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title text-muted">Tipos de acao</h6>
            <h3 class="text-warning">{{ $estatisticas['tipos_acao'] }}</h3>
        </div>
    </div>
</div>

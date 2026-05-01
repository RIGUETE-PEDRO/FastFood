<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title text-muted">Total de Registros</h6>
            <h3 class="text-primary">{{ $auditorias->total() }}</h3>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title text-muted">
                <i class="fas fa-clock text-warning"></i> Última Ação
            </h6>
            @if($auditorias->first())
                <p class="auditoria-timestamp mb-1">
                    {{ $auditorias->first()->created_at?->format('d/m/Y') }}
                </p>
                <small class="text-muted">
                    {{ $auditorias->first()->created_at?->format('H:i:s') }}
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
            <h6 class="card-title text-muted">Usuários Ativos</h6>
            <h3 class="text-info">{{ $auditorias->groupBy('usuario_id')->count() }}</h3>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card">
        <div class="card-body">
            <h6 class="card-title text-muted">Tipos de Ação</h6>
            <h3 class="text-warning">{{ $auditorias->groupBy('acao')->count() }}</h3>
        </div>
    </div>
</div>

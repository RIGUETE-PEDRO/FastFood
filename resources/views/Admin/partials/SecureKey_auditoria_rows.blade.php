@forelse($auditorias as $auditoria)
    <tr>
        <td><small class="text-muted">#{{ $auditoria->id }}</small></td>
        <td>
            @if($auditoria->usuario)
                <strong>{{ $auditoria->usuario->nome }}</strong><br>
                <small class="text-muted">{{ $auditoria->usuario->email }}</small>
            @else
                <span class="badge badge-warning">Usuário deletado</span>
            @endif
        </td>
        <td>
            <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $auditoria->acao)) }}</span>
        </td>
        <td>
            @if($auditoria->recurso)
                <code>{{ $auditoria->recurso }}</code>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            <small>{{ $auditoria->ip ?? '—' }}</small>
        </td>
        <td>
            <small class="text-muted" title="{{ $auditoria->user_agent }}">
                {{ Str::limit($auditoria->user_agent ?? '—', 30) }}
            </small>
        </td>
        <td>
            <small class="text-muted">
                <i class="far fa-calendar"></i> {{ $auditoria->created_at?->format('d/m/Y H:i:s') ?? '—' }}
            </small>
        </td>
        <td>
            @if($auditoria->detalhes)
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalDetalhes{{ $auditoria->id }}">
                    <i class="fas fa-eye"></i> Ver
                </button>

                <div class="modal fade auditoria-modal" id="modalDetalhes{{ $auditoria->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detalhes - Auditoria #{{ $auditoria->id }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <pre><code>{{ json_encode($auditoria->detalhes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8">
            <div class="auditoria-empty">
                <i class="fas fa-inbox"></i>
                <p>Nenhum registro de auditoria encontrado.</p>
            </div>
        </td>
    </tr>
@endforelse

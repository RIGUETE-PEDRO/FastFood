@forelse($auditorias as $auditoria)
    @php
        $detalhes = is_array($auditoria->detalhes) ? $auditoria->detalhes : [];
        $requisicao = $detalhes['requisicao'] ?? [];
        $resposta = $detalhes['resposta'] ?? [];
        $execucao = $detalhes['execucao'] ?? [];
        $jsonDetalhes = json_encode(
            $detalhes,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    @endphp
    <tr>
        <td class="col-id"><span class="auditoria-id">#{{ $auditoria->id }}</span></td>
        <td class="col-user">
            @if($auditoria->usuario)
                <div class="auditoria-user">
                    <span class="auditoria-user__avatar" aria-hidden="true">
                        {{ Str::upper(Str::substr($auditoria->usuario->nome ?? 'U', 0, 1)) }}
                    </span>
                    <div class="auditoria-user__text">
                        <strong title="{{ $auditoria->usuario->nome }}">{{ $auditoria->usuario->nome }}</strong>
                        <small title="{{ $auditoria->usuario->email }}">{{ $auditoria->usuario->email }}</small>
                    </div>
                </div>
            @else
                <span class="badge badge-warning">Usuario deletado</span>
            @endif
        </td>
        <td class="col-action">
            <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $auditoria->acao)) }}</span>
        </td>
        <td class="col-resource">
            @if($auditoria->recurso)
                <code title="{{ $auditoria->recurso }}">{{ $auditoria->recurso }}</code>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td class="col-ip">
            <span class="auditoria-ip">{{ $auditoria->ip ?? '-' }}</span>
        </td>
        <td class="col-agent">
            <span class="auditoria-agent" title="{{ $auditoria->user_agent ?? '-' }}">
                {{ $auditoria->user_agent ?? '-' }}
            </span>
        </td>
        <td class="col-date">
            <time class="auditoria-date" datetime="{{ $auditoria->created_at?->toIso8601String() }}">
                <span>{{ $auditoria->created_at?->format('d/m/Y') ?? '-' }}</span>
                <small>{{ $auditoria->created_at?->format('H:i:s') ?? '' }}</small>
            </time>
        </td>
        <td class="col-details">
            @if($auditoria->detalhes)
                <button class="btn btn-sm btn-outline-info auditoria-detail-btn" data-bs-toggle="modal" data-bs-target="#modalDetalhes{{ $auditoria->id }}" aria-label="Ver detalhes da auditoria {{ $auditoria->id }}">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span>Ver</span>
                </button>

                <div class="modal fade auditoria-modal" id="modalDetalhes{{ $auditoria->id }}" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detalhes - Auditoria #{{ $auditoria->id }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="auditoria-detail-summary">
                                    <section class="auditoria-detail-card">
                                        <h6>Requisição</h6>
                                        <dl>
                                            <div><dt>Método</dt><dd>{{ $requisicao['metodo'] ?? ($detalhes['metodo_http'] ?? '-') }}</dd></div>
                                            <div><dt>Rota</dt><dd>{{ $requisicao['rota'] ?? ($detalhes['rota'] ?? '-') }}</dd></div>
                                            <div class="auditoria-detail-card__full"><dt>URL</dt><dd>{{ $requisicao['url'] ?? ($detalhes['path'] ?? '-') }}</dd></div>
                                            <div><dt>IP</dt><dd>{{ $requisicao['ip'] ?? ($detalhes['ip'] ?? $auditoria->ip ?? '-') }}</dd></div>
                                            <div><dt>Controlador</dt><dd>{{ $requisicao['controlador'] ?? '-' }}</dd></div>
                                        </dl>
                                    </section>

                                    <section class="auditoria-detail-card">
                                        <h6>Resposta e execução</h6>
                                        <dl>
                                            <div><dt>Status HTTP</dt><dd>{{ $resposta['status'] ?? ($detalhes['status_http'] ?? '-') }}</dd></div>
                                            <div><dt>Resultado</dt><dd>{{ ($resposta['sucesso'] ?? false) ? 'Sucesso' : 'Falha ou redirecionamento' }}</dd></div>
                                            <div><dt>Duração</dt><dd>{{ isset($execucao['duracao_ms']) ? $execucao['duracao_ms'] . ' ms' : '-' }}</dd></div>
                                            <div><dt>Memória</dt><dd>{{ isset($execucao['memoria_mb']) ? $execucao['memoria_mb'] . ' MB' : '-' }}</dd></div>
                                            <div class="auditoria-detail-card__full"><dt>Redirecionamento</dt><dd>{{ $resposta['redirecionamento'] ?? '-' }}</dd></div>
                                        </dl>
                                    </section>
                                </div>

                                <section class="auditoria-json">
                                    <div class="auditoria-json__header">
                                        <div>
                                            <h6>JSON completo</h6>
                                            <span>Dados sanitizados da requisição e da resposta.</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary auditoria-copy-json" data-copy-target="auditoria-json-{{ $auditoria->id }}">
                                            <i class="fas fa-copy" aria-hidden="true"></i>
                                            Copiar JSON
                                        </button>
                                    </div>
                                    <pre id="auditoria-json-{{ $auditoria->id }}"><code>{{ $jsonDetalhes ?: '{}' }}</code></pre>
                                </section>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8">
            <div class="auditoria-empty">
                <i class="fas fa-inbox" aria-hidden="true"></i>
                <p>Nenhum registro de auditoria encontrado.</p>
            </div>
        </td>
    </tr>
@endforelse

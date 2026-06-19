<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <title>Auditoria SecureKey</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/Admin/SecureKey.css') }}?v={{ filemtime(public_path('css/Admin/SecureKey.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Auditoria.css') }}?v={{ filemtime(public_path('css/Admin/Auditoria.css')) }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">
                <span class="kc-brand__icon" aria-hidden="true">&#128274;</span>
                <span>SecureKey</span>
            </div>

            <nav class="kc-nav" aria-label="Navegacao SecureKey">
                <a href="{{ route('SecureKey.index') }}" class="kc-link"><span aria-hidden="true">&#8962;</span>Visao geral</a>
                <a href="{{ route('SecureKey.grupo') }}" class="kc-link"><span aria-hidden="true">&#128101;</span>Grupos</a>
                <a href="{{ route('SecureKey.permissoes') }}" class="kc-link"><span aria-hidden="true">&#128273;</span>Criar roles</a>
                <a href="{{ route('SecureKey.auditoria') }}" class="kc-link active"><span aria-hidden="true">&#128221;</span>Auditoria</a>
                <a href="{{ route('admin.bemvindo') }}" class="kc-link kc-link-login"><span aria-hidden="true">&#8592;</span>Voltar ao sistema</a>
            </nav>
        </aside>

        <main class="kc-content auditoria-container">
            <section class="auditoria-header">
                <span class="kc-kicker">Historico de seguranca</span>
                <h1>Auditoria SecureKey</h1>
                <p>Historico de acoes, eventos de autenticacao e seguranca do sistema.</p>
            </section>

            <section class="card auditoria-filters mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-filter" aria-hidden="true"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('SecureKey.auditoria') }}" class="auditoria-filter-form">
                        <div class="auditoria-filter-field">
                            <label for="filtro" class="form-label">Tipo de filtro</label>
                            <select name="filtro" id="filtro" class="form-select">
                                <option value="">Selecione</option>
                                <option value="acao" {{ request('filtro') === 'acao' ? 'selected' : '' }}>Por acao</option>
                                <option value="usuario" {{ request('filtro') === 'usuario' ? 'selected' : '' }}>Por usuario</option>
                                <option value="recurso" {{ request('filtro') === 'recurso' ? 'selected' : '' }}>Por recurso</option>
                            </select>
                        </div>

                        <div class="auditoria-filter-field auditoria-filter-field--wide">
                            <label for="valor" class="form-label">Valor</label>
                            <input type="text" name="valor" id="valor" class="form-control" placeholder="Digite o valor..." value="{{ request('valor') }}">
                        </div>

                        <div class="auditoria-filter-field">
                            <label for="ordem_data" class="form-label">Data</label>
                            <select name="ordem_data" id="ordem_data" class="form-select">
                                <option value="desc" {{ (request('ordem_data', 'desc') === 'desc') ? 'selected' : '' }}>Mais recentes</option>
                                <option value="asc" {{ request('ordem_data') === 'asc' ? 'selected' : '' }}>Mais antigas</option>
                            </select>
                        </div>

                        <div class="auditoria-filter-field">
                            <label for="data_inicio" class="form-label">De</label>
                            <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                        </div>

                        <div class="auditoria-filter-field">
                            <label for="data_fim" class="form-label">Ate</label>
                            <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
                        </div>

                        <div class="auditoria-filter-actions">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search" aria-hidden="true"></i> Buscar
                            </button>
                            <a href="{{ route('SecureKey.auditoria') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-redo" aria-hidden="true"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="card auditoria-table">
                <div class="card-header auditoria-table-header">
                    <h5><i class="fas fa-shield-alt" aria-hidden="true"></i> Registros de auditoria</h5>
                    <div class="auditoria-table-actions">
                        <span class="badge bg-light text-dark">{{ $auditorias->total() }} registros</span>
                        <a href="{{ request()->fullUrl() }}" class="btn btn-sm btn-outline-info auditoria-refresh-btn" aria-label="Atualizar registros de auditoria">
                            <i class="fas fa-rotate-right" aria-hidden="true"></i>
                            <span>Atualizar</span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive auditoria-table-scroll">
                    <table class="table table-hover mb-0 auditoria-records-table">
                        <thead>
                            <tr>
                                <th class="col-id">ID</th>
                                <th class="col-user">Usuario</th>
                                <th class="col-action">Acao</th>
                                <th class="col-resource">Recurso</th>
                                <th class="col-ip">IP</th>
                                <th class="col-agent">User Agent</th>
                                <th class="col-date">Data/Hora</th>
                                <th class="col-details">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody id="auditoria-table-body">
                            @include('Admin.partials.SecureKey_auditoria_rows', ['auditorias' => $auditorias])
                        </tbody>
                    </table>
                </div>

                @if($auditorias->hasPages())
                    <div class="card-footer auditoria-pagination">
                        {{ $auditorias->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </section>

            <div class="row auditoria-stats mt-4">
                @include('Admin.partials.SecureKey_auditoria_stats', [
                    'auditorias' => $auditorias,
                    'estatisticas' => $estatisticas,
                ])
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('click', async function (event) {
            const button = event.target.closest('.auditoria-copy-json');
            if (!button) return;

            const target = document.getElementById(button.dataset.copyTarget);
            if (!target) return;

            try {
                await navigator.clipboard.writeText(target.textContent.trim());
                const original = button.innerHTML;
                button.textContent = 'JSON copiado';
                window.setTimeout(() => {
                    button.innerHTML = original;
                }, 1600);
            } catch (_) {
                button.textContent = 'Não foi possível copiar';
            }
        });
    </script>
</body>

</html>

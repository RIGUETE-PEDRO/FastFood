<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoria Keycloak</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Keyclock.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Auditoria.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">KeyClock</div>

            <nav class="kc-nav">
                <a href="{{ route('keyclock.index') }}" class="kc-link">Visão geral</a>
                <a href="{{ route('keyclock.grupo') }}" class="kc-link">Grupos</a>
                <a href="{{ route('keyclock.permissoes') }}" class="kc-link">Criar roles</a>
                <a href="{{ route('keyclock.auditoria') }}" class="kc-link active">Auditoria</a>
                <a href="{{ route('login.form') }}" class="kc-link kc-link-login">Voltar para login</a>
            </nav>
        </aside>

        <main class="kc-content auditoria-container">
                <section class="auditoria-header">
                    <h1>📋 Auditoria Keycloak</h1>
                    <p>Histórico de ações, eventos de autenticação e segurança do sistema.</p>
                </section>

                <!-- Filtros -->
                <section class="card auditoria-filters mb-4">
                    <div class="card-header">
                        <h5>🔍 Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('keyclock.auditoria') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="filtro" class="form-label">Tipo de Filtro</label>
                                <select name="filtro" id="filtro" class="form-select">
                                    <option value="">-- Selecione --</option>
                                    <option value="acao" {{ request('filtro') === 'acao' ? 'selected' : '' }}>Por Ação</option>
                                    <option value="usuario" {{ request('filtro') === 'usuario' ? 'selected' : '' }}>Por Usuário</option>
                                    <option value="recurso" {{ request('filtro') === 'recurso' ? 'selected' : '' }}>Por Recurso</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="valor" class="form-label">Valor</label>
                                <input type="text" name="valor" id="valor" class="form-control" placeholder="Digite o valor..." value="{{ request('valor') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="ordem_data" class="form-label">Data</label>
                                <select name="ordem_data" id="ordem_data" class="form-select">
                                    <option value="desc" {{ (request('ordem_data', 'desc') === 'desc') ? 'selected' : '' }}>Mais recentes</option>
                                    <option value="asc" {{ request('ordem_data') === 'asc' ? 'selected' : '' }}>Mais antigas</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="data_inicio" class="form-label">De</label>
                                <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="data_fim" class="form-label">Até</label>
                                <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ request('data_fim') }}">
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('keyclock.auditoria') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-redo"></i> Limpar
                                </a>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Tabela de Auditoria -->
                <section class="card auditoria-table">
                    <div id="auditoria-config" data-url="{{ route('keyclock.auditoria') }}" hidden></div>
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>📊 Registros de Auditoria</h5>
                        <span class="badge bg-light text-dark" id="auditoria-total-badge">{{ $auditorias->total() }} registros</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>Ação</th>
                                    <th>Recurso</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                    <th>Data/Hora</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="auditoria-table-body">
                                @include('Admin.partials.keyclock_auditoria_rows', ['auditorias' => $auditorias])
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    @if($auditorias->hasPages())
                        <div class="card-footer auditoria-pagination">
                            {{ $auditorias->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </section>

                <!-- Estatísticas Rápidas -->
                <div class="row auditoria-stats mt-4" id="auditoria-stats-container">
                    @include('Admin.partials.keyclock_auditoria_stats', ['auditorias' => $auditorias])
                </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const cfg = document.getElementById('auditoria-config');
            const auditoriaUrl = cfg?.dataset?.url || '';
            const tbody = document.getElementById('auditoria-table-body');
            const stats = document.getElementById('auditoria-stats-container');
            const badge = document.getElementById('auditoria-total-badge');

            if (!auditoriaUrl || !tbody || !stats || !badge) {
                return;
            }

            const form = document.querySelector(`form[action="${auditoriaUrl}"]`);

            const montarQuery = () => {
                const params = new URLSearchParams(window.location.search);
                params.set('polling', '1');

                if (form) {
                    const formData = new FormData(form);
                    for (const [key, value] of formData.entries()) {
                        if (value !== null && String(value).trim() !== '') {
                            params.set(key, String(value));
                        } else {
                            params.delete(key);
                        }
                    }
                }

                return params.toString();
            };

            const atualizarTabela = async () => {
                try {
                    const query = montarQuery();
                    const response = await fetch(`${auditoriaUrl}?${query}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    if (!data || !data.rowsHtml) {
                        return;
                    }

                    tbody.innerHTML = data.rowsHtml;
                    stats.innerHTML = data.statsHtml;
                    badge.textContent = `${data.total} registros`;
                } catch (e) {
                    console.warn('Falha ao atualizar auditoria em tempo real.', e);
                }
            };

            setInterval(atualizarTabela, 5000);
        })();
    </script>
</body>

</html>


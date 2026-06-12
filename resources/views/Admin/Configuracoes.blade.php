<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>Configurações | Admin</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}?v={{ filemtime(public_path('css/Admin/Principal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Configuracoes.css') }}?v={{ filemtime(public_path('css/Admin/Configuracoes.css')) }}">
</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>

            <main class="config-admin">
                <section class="config-hero painel">
                    <div>
                        <span class="config-hero__tag">Configurações</span>
                        <h1>Preferências do sistema</h1>
                        <p>Controle recursos administrativos usados no atendimento.</p>
                    </div>
                    <div class="config-hero__badge">
                        <span>Acesso</span>
                        <strong>{{ $tipoUsuario ?? 'Administrador' }}</strong>
                    </div>
                </section>

                <section class="config-grid">
                    <article class="config-card painel">
                        <div>
                            <h2>Notificações de pedidos</h2>
                            <p>Receba um aviso do navegador quando chegar pedido pendente para aceitar.</p>
                        </div>

                        <div class="config-card__actions">
                            <button type="button" class="btn-config-notification" id="pedidos-enable-notifications">
                                Ativar notificações
                            </button>
                            <span class="config-card__hint" id="notification-settings-status">
                                As notificações dependem da permissão do navegador.
                            </span>
                        </div>
                    </article>
                </section>
                <section class="config-grid">
                    <form class="empresa-form" action="{{ route('admin.configuracoes.atualizar') }}" method="POST">
                        @csrf

                        <article class="config-card config-card--form painel">
                            <div class="empresa-form__header">
                                <div>
                                    <h2 class="titulo">Dados da empresa</h2>
                                    <p class="titulo">Informacoes usadas na identificacao da empresa, endereco e impressao da comanda.</p>
                                </div>
                            </div>

                            <div class="empresa-form__grid">
                                @foreach ($camposEmpresa as $campo)
                                    <label class="empresa-field {{ $campo['type'] === 'textarea' ? 'empresa-field--full' : '' }}">
                                        <span>{{ $campo['label'] }}</span>
                                        @if ($campo['type'] === 'textarea')
                                            <textarea name="dados_empresa[{{ $campo['informacao'] }}]" rows="3">{{ old('dados_empresa.' . $campo['informacao'], $campo['value']) }}</textarea>
                                        @else
                                            <input
                                                type="{{ $campo['type'] }}"
                                                name="dados_empresa[{{ $campo['informacao'] }}]"
                                                value="{{ old('dados_empresa.' . $campo['informacao'], $campo['value']) }}"
                                            >
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                            <div class="empresa-form__actions">
                                <button type="submit" class="btn-config-save">Salvar configuracoes</button>
                            </div>
                        </article>
                    </form>
                </section>
            </main>
        </div>
    </div>
    @include('components.flash-toast')
</body>

</html>

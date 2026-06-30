<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @include('partials.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SecureKey - Grupos</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/SecureKey.css') }}">
    <script src="{{ asset('js/Admin/SecureKey-grupo.js') }}" defer></script>
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
                <a href="{{ route('SecureKey.grupo') }}" class="kc-link active"><span aria-hidden="true">&#128101;</span>Grupos</a>
                <a href="{{ route('SecureKey.permissoes') }}" class="kc-link"><span aria-hidden="true">&#128273;</span>Criar roles</a>
                <a href="{{ route('admin.bemvindo') }}" class="kc-link kc-link-login"><span aria-hidden="true">&#8592;</span>Voltar ao sistema</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <span class="kc-kicker">Permissoes por perfil</span>
                <h1>Grupos</h1>
                <p>Defina quais roles cada grupo pode acessar no sistema.</p>
            </header>

            <section class="kc-card">
                <h2>Atribuicao de role por grupo</h2>
                <p>As roles salvas aqui sao aplicadas ao tipo de usuario do grupo.</p>
            </section>

            <section class="kc-users-list">
                <div
                    id="kc-grupo-role-source"
                    data-roles='@json($roles->map(fn($role) => ["id" => $role->id, "nome" => $role->nome])->values())'
                    data-add-url-template="{{ route('SecureKey.grupo.roles.store', ['grupo' => '__GRUPO__']) }}"
                    data-remove-url-template="{{ route('SecureKey.grupo.roles.destroy', ['grupo' => '__GRUPO__', 'role' => '__ROLE__']) }}"
                    hidden
                ></div>

                @forelse ($grupos as $grupo)
                    @php
                        $rolesDoGrupo = $rolesPorGrupo[$grupo->id] ?? [];
                    @endphp
                    <article class="kc-user-row">
                        <button
                            type="button"
                            class="kc-user-toggle"
                            data-target="roles_grupo_{{ $grupo->id }}"
                            aria-expanded="false"
                        >
                            <div class="kc-user-info">
                                <strong>{{ $grupo->nome }}</strong>
                                <span>{{ count($rolesDoGrupo) }} roles vinculadas</span>
                            </div>
                            <span class="kc-user-toggle__icon">&#9662;</span>
                        </button>

                        <div id="roles_grupo_{{ $grupo->id }}" class="kc-roles-expanded" hidden>
                            <h3>Roles do grupo</h3>

                            <div class="kc-role-add-row">
                                <div class="kc-autocomplete">
                                    <input
                                        type="text"
                                        id="role_input_grupo_{{ $grupo->id }}"
                                        class="kc-role-input"
                                        autocomplete="off"
                                        placeholder="Digite uma role"
                                    >
                                    <ul class="kc-autocomplete-list" hidden></ul>
                                </div>
                                <button type="button" class="kc-btn kc-role-add-btn" data-grupo-id="{{ $grupo->id }}">Adicionar</button>
                            </div>

                            <ul class="kc-role-added-list">
                                @forelse ($rolesDoGrupo as $role)
                                    <li class="kc-role-added-item" data-role-id="{{ $role['id'] }}" data-grupo-id="{{ $grupo->id }}">
                                        <span>{{ $role['nome'] }}</span>
                                        <button type="button" class="kc-role-remove-btn">Remover</button>
                                    </li>
                                @empty
                                    <li class="kc-role-added-item kc-role-added-item--empty">
                                        <span>Nenhuma role adicionada.</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </article>
                @empty
                    <article class="kc-empty">
                        <p>Nenhum grupo encontrado.</p>
                    </article>
                @endforelse
            </section>

        </main>
    </div>
</body>

</html>

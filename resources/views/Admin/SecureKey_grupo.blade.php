<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KeyClock - Grupos</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Keyclock.css') }}">
    <script src="{{ asset('js/Admin/keyclock-grupo.js') }}" defer></script>
</head>

<body>
    <div class="kc-layout">
        <aside class="kc-sidebar">
            <div class="kc-brand">KeyClock</div>

            <nav class="kc-nav">
                <a href="{{ route('keyclock.index') }}" class="kc-link">Visão geral</a>

                <a href="{{ route('keyclock.grupo') }}" class="kc-link active">Grupos</a>
                <a href="{{ route('keyclock.permissoes') }}" class="kc-link">Criar roles</a>
                <a href="{{ route('keyclock.auditoria') }}" class="kc-link">Auditoria</a>
                <a href="{{ route('login.form') }}" class="kc-link kc-link-login">Voltar para login</a>
            </nav>
        </aside>

        <main class="kc-content">
            <header class="kc-header">
                <h1>Grupos</h1>
                <p>Defina uma role padrão para cada grupo.</p>
            </header>

            <section class="kc-card">
                <h2>Atribuição de role por grupo</h2>
                <p>As roles salvas aqui são aplicadas ao tipo de usuário do grupo.</p>
            </section>

            <section class="kc-users-list">
                <div
                    id="kc-grupo-role-source"
                    data-roles='@json($roles->map(fn($role) => ["id" => $role->id, "nome" => $role->nome])->values())'
                    data-add-url-template="{{ route('keyclock.grupo.roles.store', ['grupo' => '__GRUPO__']) }}"
                    data-remove-url-template="{{ route('keyclock.grupo.roles.destroy', ['grupo' => '__GRUPO__', 'role' => '__ROLE__']) }}"
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

                            </div>
                            <span class="kc-user-toggle__icon">▾</span>
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
                                        <button type="button" class="kc-role-remove-btn">Remover</button>
                                        <span>{{ $role['nome'] }}</span>
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

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="{{ asset('css/Admin/Perfil.css') }}">
</head>
<body class="profile-page">
    @php
        $roleLabel = $tipoUsuario ?? ($usuario->tipo_usuario_id == 1 ? 'Administrador' : 'Usuário Comum');
    @endphp
    <div class="profile-page__wrapper">
        <div class="profile-page__topbar">
            <a href="{{ route('Administrativo') }}" class="profile-back-link">&larr; Voltar</a>
        </div>

        @if(session('sucesso'))
            <div class="alert alert-success">{{ session('sucesso') }}</div>
        @endif

        @if(session('erro'))
            <div class="alert alert-danger">{{ session('erro') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="profile-card">
            <aside class="profile-card__summary">
                <div class="profile-avatar">
                    <img class="profile-image" src="{{ asset('img/pessoa.avif') }}" alt="Foto do usuário">
                    <span class="profile-role-badge">{{ $roleLabel }}</span>
                </div>

                <h1 class="profile-title">{{ $usuario->nome }}</h1>
                <p class="profile-subtitle">Gerencie seus dados de acesso e mantenha suas informações sempre atualizadas.</p>

                <div class="profile-details-grid">
                    <div class="profile-detail">
                        <span class="profile-detail__label">E-mail</span>
                        <span class="profile-detail__value">{{ $usuario->email }}</span>
                    </div>
                    <div class="profile-detail">
                        <span class="profile-detail__label">Telefone</span>
                        <span class="profile-detail__value">{{ $usuario->telefone ?? 'Não informado' }}</span>
                    </div>
                    <div class="profile-detail">
                        <span class="profile-detail__label">Membro desde</span>
                        <span class="profile-detail__value">{{ optional($usuario->created_at)->format('d/m/Y') ?? 'Indisponível' }}</span>
                    </div>
                </div>
            </aside>

            <section class="profile-card__form">
                <div class="profile-form-header">
                    <h2>Dados da conta</h2>
                    <p>Atualize suas informações pessoais e mantenha seus contatos sempre corretos.</p>
                </div>

                <form action="{{ route('Alterar_Dados') }}" method="POST" class="profile-form">
                    @csrf

                    <div class="input-group">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" required>
                    </div>

                    <div class="input-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                    </div>

                    <div class="input-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" value="{{ old('telefone', $usuario->telefone) }}" placeholder="(00) 00000-0000">
                    </div>

                    <div class="profile-form__actions">
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        <a href="{{ route('Administrativo') }}" class="btn btn-ghost">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>
</html>

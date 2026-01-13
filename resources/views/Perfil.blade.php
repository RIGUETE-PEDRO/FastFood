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
                    <div class="profile-image-wrapper">
                        <img class="profile-image" id="preview-image" src="{{ $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.png') }}" alt="Foto do usuário">
                        <label for="foto-upload" class="profile-image-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                            </svg>
                            <span>Alterar foto</span>
                        </label>
                    </div>
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

                <form action="{{ route('Alterar_Dados') }}" method="POST" enctype="multipart/form-data" class="profile-form" id="form-perfil">
                    @csrf

                    <!-- Input de imagem oculto dentro do formulário -->
                    <input type="file" id="foto-upload" name="url_imagem_perfil" accept="image/*" style="display: none;">

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

    <script>
        // Preview da imagem ao selecionar arquivo
        document.getElementById('foto-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview-image').src = event.target.result;
                };
                reader.readAsDataURL(file);

                // Auto-submit do formulário (opcional)
                // document.querySelector('.profile-form').submit();
            }
        });
    </script>
</body>
</html>

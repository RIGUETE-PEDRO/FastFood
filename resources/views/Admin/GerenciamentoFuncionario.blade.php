<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Funcionários</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/GerenciamentoFuncionario.css') }}">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary navbar">
        <div class="container-fluid navbar">
            <a class="navbar-brand text titulo" href="#">FlashFood</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll navbar" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <a class="nav-link active text" aria-current="page" href="{{ route('Administrativo') }}">DashBoard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="{{ route('gerenciamento_funcionarios') }}">Gerenciamento de Funcionarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Cardápio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text navegador" href="#">Entregas</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text navegador" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Produtos
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end list">
                            <li><a class="dropdown-item text" href="#">Gerenciamento de produtos</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text" href="#">Estoque</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true"></a>
                    </li>
                    <!-- Menu do usuário -->
                    <li class="nav-item dropdown ms-auto">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="circulo_maior">
                                <img class="profile-image" id="preview-image" src="{{ $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.avif') }}" alt="Foto do usuário">
                                <label for="foto-upload" class="profile-image-overlay">
                            </div>
                            <span class="ms-2 text">{{ $nomeUsuario }}</span>


                        </a>
                        <ul class="dropdown-menu dropdown-menu-end list">
                            <li><a class="dropdown-item text" href="{{ route('perfil') }}">Perfil</a></li>
                            <li><a class="dropdown-item text" href="#">Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text" href="{{ route('logout') }}">Sair</a></li>
                        </ul>


                    </li>


                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="gerenciamento-container">
        <div class="header-section">
            <h1 class="page-title">Gerenciamento de Funcionários</h1>
            <button class="btn btn-primary btn-add" type="button" id="openCreateUser">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                </svg>
                Adicionar Funcionário
            </button>
        </div>

        @if(session('sucesso'))
        <div class="alert alert-success">{{ session('sucesso') }}</div>
        @endif

        @if(session('erro'))
        <div class="alert alert-danger">{{ session('erro') }}</div>
        @endif

        <!-- Tabela de Funcionários -->
        <div class="table-card">
            <div class="table-header">
                <h2>Lista de Funcionários</h2>
                <form method="GET" action="{{ route('funcionarios.buscar') }}">
                    <div class="search-box">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar funcionário..."
                            class="search-input">

                </form>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                </svg>
            </div>
        </div>

        @if($lista && count($lista) > 0)
        <div class="table-responsive">
            <table class="funcionarios-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @foreach($lista as $funcionario)
                    <tr>
                        <td>
                            <div class="foto-wrapper">
                                <img src="{{ $funcionario->url_imagem_perfil ? asset('img/perfil/' . $funcionario->url_imagem_perfil) : asset('img/pessoa.avif') }}" alt="Foto" class="foto-funcionario">
                            </div>
                        </td>
                        <td class="nome-cell">{{ $funcionario->nome }}</td>
                        <td>{{ $funcionario->email }}</td>
                        <td>{{ $funcionario->telefone ?? 'Não informado' }}</td>
                        <td>
                            @if ($funcionario->tipo_usuario_id == 2)
                            <span class="badge badge-tipo-estabelecimento">
                                {{ $funcionario->tipo_descricao }}
                            </span>
                            @elseif ($funcionario->tipo_usuario_id == 3)
                            <span class="badge badge-tipo-administrador">
                                {{ $funcionario->tipo_descricao }}
                            </span>
                            @elseif ($funcionario->tipo_usuario_id == 4)
                            <span class="badge badge-tipo-entregador">
                                {{ $funcionario->tipo_descricao }}
                            </span>
                            @else
                            <span class="badge badge-tipo">
                                {{ $funcionario->tipo_descricao }}
                            </span>
                            @endif
                        </td>
                        <td>
                            @if(isset($funcionario->has_ativo) && $funcionario->has_ativo)
                            <span class="badge badge-ativo">Ativo</span>
                            @else
                            <span class="badge badge-inativo">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit"
                                    title="Editar"
                                    data-id="{{ $funcionario->usuario_id ?? $funcionario->id }}"
                                    data-nome="{{ $funcionario->nome }}"
                                    data-email="{{ $funcionario->email }}"
                                    data-telefone="{{ $funcionario->telefone }}"
                                    data-salario="{{ $funcionario->salario ?? '' }}"
                                    data-tipo="{{ $funcionario->tipo_usuario_id }}"
                                    data-ativo="{{ isset($funcionario->has_ativo) && $funcionario->has_ativo ? 1 : 0 }}"
                                    data-action="{{ route('funcionarios.atualizar', $funcionario->usuario_id ?? $funcionario->id) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                                    </svg>
                                </button>
                                <button class="btn-action btn-delete"
                                    title="Excluir"
                                    data-id="{{ $funcionario->usuario_id ?? $funcionario->id }}"
                                    data-nome="{{ $funcionario->nome }}"
                                    data-action="{{ route('funcionarios.deletar', $funcionario->usuario_id ?? $funcionario->id) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
            </svg>
            <h3>Nenhum funcionário encontrado</h3>
            <p>Adicione funcionários para começar a gerenciar sua equipe.</p>
        </div>
        @endif
        </div>
    </main>

    <!-- Overlay de novo usuário -->
    <div class="overlay-backdrop" id="createUserOverlay">
        <div class="overlay-panel">
            <div class="overlay-header">
                <div>
                    <p class="overlay-badge">Novo usuário</p>
                    <h3>Cadastrar funcionário</h3>
                    <p class="overlay-subtitle">Preencha os dados para adicionar alguém à equipe.</p>
                </div>
                <button type="button" class="overlay-close" id="closeCreateUser" aria-label="Fechar">
                    ✕
                </button>
            </div>

            <form action="{{ route('CadastrarFuncionario') }}" method="POST" class="overlay-form" id="formCreateUser" data-default-action="{{ route('CadastrarFuncionario') }}">
                @csrf
                <input type="hidden" name="usuario_id" id="usuario_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome completo</label>
                        <input type="text" name="nome" id="nome" class="form-control" placeholder="Ex: Maria Silva" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="email@empresa.com" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" name="telefone" id="telefone" class="form-control" placeholder="(11) 99999-9999">
                    </div>

                    <div class="form-group">
                        <label for="tipo_usuario_id">Tipo de usuário</label>
                        <select name="tipo_usuario_id" id="tipo_usuario_id" class="form-control" required>
                            <option value="" selected disabled>Selecione</option>
                            <option value="2">Estabelecimento</option>
                            <option value="3">Administrador</option>
                            <option value="4">Entregador</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="has_ativo">Status</label>
                        <select name="has_ativo" id="has_ativo" class="form-control" required>
                            <option value="" selected disabled>Selecione</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    <div class="form-group salario-group">
                        <label for="salario">Salário</label>
                        
                        <input
                            type="text"
                            name="salario"
                            id="salario"
                            class="form-control"
                            placeholder="0,00"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" id="senha" class="form-control" placeholder="digite a senha..." required>
                    </div>

                    <div class="form-group">
                        <label for="senha_confirmation">Confirmar senha</label>
                        <input type="password" name="senha_confirmation" id="senha_confirmation" class="form-control" placeholder="digite a senha..." required>
                    </div>
                </div>

                <div class="overlay-actions">
                    <button type="button" class="btn-secondary" id="cancelCreateUser">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="overlay-backdrop" id="deleteConfirmOverlay">
        <div class="overlay-panel overlay-small">
            <div class="overlay-header">
                <div>
                    <p class="overlay-badge badge-danger">Confirmação</p>
                    <h3>Excluir funcionário</h3>
                    <p class="overlay-subtitle">Essa ação não pode ser desfeita.</p>
                </div>
                <button type="button" class="overlay-close" id="closeDeleteConfirm" aria-label="Fechar">
                    ✕
                </button>
            </div>

            <form method="POST" class="overlay-form" id="deleteForm">
                @csrf
                <p id="deleteConfirmText">Tem certeza que deseja excluir?</p>

                <div class="overlay-actions">
                    <button type="button" class="btn-secondary" id="cancelDelete">Cancelar</button>
                    <button type="submit" class="btn-secondary">Excluir</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/gerenciamento-funcionario.js') }}"></script>
</body>

</html>

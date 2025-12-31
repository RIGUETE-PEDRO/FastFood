<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Produtos</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/GerenciamentoProduto.css') }}">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary navbar">
        <div class="container-fluid navbar">
            <a class="navbar-brand text titulo" href="#">FlashFood</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll navbar" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <a class="nav-link active text" aria-current="page" href="#">DashBoard</a>
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
                            <li><a class="dropdown-item text" href="{{ route('gerenciamento_produtos') }}">Gerenciamento de produtos</a></li>
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
                                <img class="profile-image" id="preview-image" src="{{ $usuario->url_imagem_perfil ? asset('img/perfil/' . $usuario->url_imagem_perfil) : asset('img/person.png') }}" alt="Foto do usuário">
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
    <main>
        <div class="gerenciamento-container">
            <div class="header-section">
                <h1 class="page-title">Produtos</h1>
                <button id="openCreateProduct" class="btn-add">Adicionar Produto</button>
            </div>
            <div class="table-card">
                <div class="table-header">
                    <h2>Lista de Produtos</h2>
                    <div class="search-box">
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar produto...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="produtos-table">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Status</th>
                                <th>Categoria</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($produtos as $produto)
                            <tr>
                                <td>
                                    @if(!empty($produto->imagem_url))
                                    <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="Imagem do produto" style="width:48px; height:48px; object-fit:cover; border-radius:8px;">
                                    @else
                                    <span style="color:#aaa;">Sem imagem</span>
                                    @endif
                                </td>
                                <td class="nome-cell">{{ $produto->nome }}</td>
                                <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $produto->disponivel ? 'bg-success' : 'bg-danger' }}">
                                        {{ $produto->disponivel ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>

                                <td>{{ $produto->categoria->nome ?? '-' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Ações na tabela: -->
                                        <button class="btn-action btn-edit"
                                            title="Editar"
                                            data-nome="{{ $produto->nome }}"
                                            data-preco="{{ $produto->preco }}"
                                            data-descricao="{{ $produto->descricao }}"
                                            data-categoria-id="{{ $produto->categoria_id }}"
                                            data-ativo="{{ $produto->disponivel ? 1 : 0 }}"
                                            data-imagem-url="{{ $produto->imagem_url }}"
                                            data-action="{{ route('produtos.atualizar', $produto->id) }}"

                                            >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                                            </svg>
                                        </button>
                                        <button class="btn-action btn-delete"
                                            title="Excluir"
                                            data-nome="{{ $produto->nome }}"
                                            data-produto-id="{{ $produto->id }}"
                                            data-usuario-id="{{ $usuario->usuario_id ?? $usuario->id }}"
                                            data-action="{{ route('deletar_produto', $produto->id) }}">
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
            </div>
        </div>
    <!-- Overlay de cadastro/edição de produto -->
        <div class="overlay-backdrop" id="createProductOverlay">
            <div class="overlay-panel">
                <div class="overlay-header">
                    <div>
                        <p class="overlay-badge" id="overlay-badge">Novo produto</p>
                        <h3 id="overlay-title">Cadastrar produto</h3>
                        <p class="overlay-subtitle" id="overlay-subtitle">Preencha os dados para adicionar um produto ao cardápio.</p>
                    </div>
                    <button type="button" class="overlay-close" id="closeCreateProduct" aria-label="Fechar">✕</button>
                </div>
                <form action="{{ route('Cadastrar_Produto') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Para o JS saber a rota de cadastro --}}
                    <form action="{{ route('Cadastrar_Produto') }}" method="POST" enctype="multipart/form-data" data-cadastro-action="{{ route('Cadastrar_Produto') }}">
                    <input type="hidden" name="produto_id" id="produto_id">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="produto-nome">Nome do Produto</label>
                            <input type="text" name="nome" id="produto-nome" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="produto-preco">Preço</label>
                            <input type="text" name="preco" id="produto-preco" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="produto-descricao">Descrição</label>
                            <textarea name="descricao" id="produto-descricao" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="produto-imagem">Imagem</label>
                            <input type="file" name="imagem" id="produto-imagem" class="form-control">
                            <span style="display:block; margin-top:6px; color:#981b1e; font-weight:500;">
                                <strong>Nome da imagem:</strong> <span id="imagem-nome"></span>
                            </span>
                        </div>
                        <div class="form-group">
                            <label for="produto-categoria">Categoria</label>
                            <select name="categoria_id" id="produto-categoria" class="form-control" required>
                                <option value="" selected disabled>Selecione</option>
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="produto-ativo">Status</label>
                            <button type="button" id="btnAtivo" class="btn-status active" data-ativo="1">Ativo</button>
                            <button type="button" id="btnInativo" class="btn-status" data-ativo="0">Inativo</button>
                            <input type="hidden" name="ativo" id="produto-ativo" value="1">
                        </div>
                    </div>
                    <div class="overlay-actions">
                        <button type="button" class="btn-secondary" id="cancelCreateProduct">Cancelar</button>
                        <button type="submit" class="btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <!-- Delete confirmation overlay (reused from gerenciamento de funcionários) -->
    <div class="overlay-backdrop" id="deleteConfirmOverlay">
        <div class="overlay-panel overlay-small">
            <div class="overlay-header">
                <div>
                    <p class="overlay-badge badge-danger">Confirmação</p>
                    <h3>Excluir produto</h3>
                    <p class="overlay-subtitle">Essa ação não pode ser desfeita.</p>
                </div>
                <button type="button" class="overlay-close" id="closeDeleteConfirm" aria-label="Fechar">✕</button>
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

    <script src="{{ asset('js/gerenciamento-produto.js') }}"></script>
    <script src="{{ asset('js/gerenciamento-funcionario.js') }}"></script>
</body>

</html>

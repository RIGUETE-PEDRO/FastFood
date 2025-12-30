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
                        <th>Categoria</th>
                        <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @foreach($produtos as $produto)
                                <tr>
                                    <td>
                                        @if($produto->imagem)
                                            <img src="{{ asset('img/produtos/' . $produto->imagem) }}" alt="Imagem do produto" style="width:48px; height:48px; object-fit:cover; border-radius:8px;">
                                        @else
                                            <span style="color:#aaa;">Sem imagem</span>
                                        @endif
                                    </td>
                                    <td class="nome-cell">{{ $produto->nome }}</td>
                                    <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                                    <td>{{ $produto->categoria->nome ?? '-' }}</td>
                                    <td><!-- ações --></td>
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
                        <p class="overlay-badge">Novo produto</p>
                        <h3>Cadastrar produto</h3>
                        <p class="overlay-subtitle">Preencha os dados para adicionar um produto ao cardápio.</p>
                    </div>
                    <button type="button" class="overlay-close" id="closeCreateProduct" aria-label="Fechar">✕</button>
                </div>
                <form action="#" method="POST" class="overlay-form" id="formCreateProduct" enctype="multipart/form-data">
                    @csrf
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
                            <button type="button" id="btnAtivo" class="btn-status" data-ativo="1">Ativo</button>
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
    <script src="{{ asset('js/gerenciamento-produto.js') }}"></script>
</body>
</html>

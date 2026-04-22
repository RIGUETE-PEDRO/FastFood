<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Garçom</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/GerenciamentoProduto.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Garcom.css') }}">
</head>

<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">☰</span>
                Menu
            </button>
            <main>
                <section class="dashboard-header">
                    <h1>Painel do Garçom</h1>
                    <p>Área de atendimento em construção.</p>
                </section>

                <section class="dashboard-header">
                    <h5 class="page-title">Mesa</h5>
                    <label class="form-label" for="mesa_id">Selecione uma mesa</label>
                    <select name="mesa_id" id="mesa_id" class="form-select" required>
                        <option value=""> Selecione </option>
                        @foreach ($mesas as $mesa)
                        <option value="{{ $mesa->id }}">Mesa {{ $mesa->numero_da_mesa }}</option>
                        @endforeach
                    </select>
                </section>

                <div class="gerenciamento-container">
                    <div class="header-section">
                        <h1 class="page-title">Produtos</h1>

                    </div>
                    <div class="table-card">
                        <div class="table-header">
                            <h2>Lista de Produtos</h2>
                            <div class="search-box">
                                <input type="text" id="searchInput" class="search-input" placeholder="Buscar produto...">
                            </div>
                        </div>

                        <div class="garcom-filter-wrap">
                            <form method="GET" class="garcom-filter-form" aria-label="Filtro por categoria">
                                <div class="garcom-filter-field">
                                    <label for="categoria_id" class="garcom-filter-label">Categoria</label>
                                    <select name="categoria_id" id="categoria_id" class="garcom-filter-select">
                                        <option value="">Todas as categorias</option>
                                        @foreach(($categorias ?? []) as $categoria)
                                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="garcom-filter-actions">
                                    <button type="submit" class="btn-add garcom-filter-btn">Filtrar</button>
                                    <button type="button" class="garcom-filter-clear">Limpar</button>
                                </div>
                            </form>
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
                                            @if(!empty($produto->imagem_url))
                                            <img src="{{ asset('img/produtos/' . $produto->imagem_url) }}" alt="Produto {{ $produto->nome }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px;">
                                            @else
                                            <span style="color:#aaa;">Sem imagem</span>
                                            @endif
                                        </td>
                                        <td class="nome-cell">{{ $produto->nome }}</td>
                                        <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>


                                        <td>{{ $produto->categoria->nome ?? '-' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <form action="{{ route('garcom.adicionar-produto') }}" method="POST" class="add-produto-form">
                                                    @csrf
                                                    <input type="hidden" name="produto_id" value="{{ $produto->id }}">
                                                    <input type="hidden" name="mesa_id" class="mesa-id-hidden">

                                                    <div class="qtd-wrapper">
                                                        <label for="qtd_{{ $produto->id }}" class="qtd-label">Quantidade</label>
                                                        <input
                                                            id="qtd_{{ $produto->id }}"
                                                            type="number"
                                                            name="quantidade"
                                                            class="qtd-input"
                                                            min="1"
                                                            value="1"
                                                            required>
                                                    </div>

                                                    <button type="submit" class="btn-add">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                                                        </svg>
                                                        Adicionar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
            <script src="{{ asset('js/garcom.js') }}"></script>
</body>

</html>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carrinho</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Carrinho.css') }}">
</head>

<body>

    <main>
        <div class="table-corpo">
            <h1>Carrinho</h1>
            <div>
                <a href="{{ route('index') }}">voltar</a>
            </div>
            <table class="table">
                @if ($carrinho->isEmpty())
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="64"
                        height="64"
                        fill="currentColor"
                        viewBox="0 0 16 16">
                        <path d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4H3a1 1 0 0 0-1 1v8.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V5a1 1 0 0 0-1-1h-2.5v-.5A2.5 2.5 0 0 0 8 1zm-1.5 3v-.5a1.5 1.5 0 0 1 3 0V4h-3z" />
                    </svg>

                    <h3>Nenhum produto encontrado</h3>
                    <p>Adicione produtos para começar a gerenciar seu carrinho.</p>
                </div>


                @else
                <thead>
                    <tr class="title-table">
                        <th>Imagem</th>
                        <th>Produto</th>
                        <th>Preço unitário</th>
                        <th>Preço total</th>
                        <th>Quantidade</th>
                        <th>Adicionar</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($carrinho as $item)
                    <tr>
                        <td><img src="{{ asset('img/produtos/' . $item->produto->imagem_url) }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px;"></td>
                        <td>{{ $item->produto->nome }}</td>
                        <td>R${{ $item->produto->preco }}</td>
                        <td>R${{ $item->preco_total }}</td>
                        <td>
                            <!-- Formulário para atualizar a quantidade -->
                            <form data-qty-form action="{{ route('carrinho.atualizarQuantidade', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" name="acao" value="menos" class="button negativo">−</button>

                                <input class="input-quantidade"
                                    type="number"
                                    name="quantidade"
                                    min="1"
                                    value="{{ $item->quantidade }}" />
                                <button type="submit" name="acao" value="mais" class="button positivo">+</button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('carrinho.toggle', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <input
                                    type="checkbox"
                                    name="ativo"
                                    value="1"
                                    {{ $item->selecionado ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                            </form>
                        </td>

                        <td>
                            <form method="POST" action="{{ route('carrinho.remover', $item->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">Remover</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </table>
        </div>
    </main>
</body>

</html>

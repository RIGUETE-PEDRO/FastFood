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
</head>

<body>

    <main>
        <div class="container">
            <h1>Carrinho</h1>
            <div>
                <a href="{{ route('index') }}">voltar</a>
            </div>
            <table class="table">
                <thead>
                    <tr>
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
                        <td><img src="{{ asset('img/produtos/' . $item->produto->imagem_url) }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px;" ></td>
                        <td>{{ $item->produto->nome }}</td>
                        <td>R${{ $item->produto->preco }}</td>
                        <td>R${{ $item->preco_total }}</td>
                        <td>
                            <!-- Formulário para atualizar a quantidade -->
                            <form data-qty-form action="{{ route('carrinho.atualizarQuantidade', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" name="acao" value="menos">−</button>

                                <input
                                    type="number"
                                    name="quantidade"
                                    min="1"
                                    value="{{ $item->quantidade }}" />


                                <button type="submit" name="acao" value="mais">+</button>
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
            </table>
        </div>
    </main>
</body>

</html>

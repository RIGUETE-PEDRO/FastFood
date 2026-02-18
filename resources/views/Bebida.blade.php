<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bebidas</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Index.css') }}">
</head>

<body>

    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">
                <span class="ff-sidebar-toggle__icon">&#9776;</span>
                Menu
            </button>
    <main>


        <div class="container-produtos mt-4">
            @foreach ($bebidas as $bebida)


            <div class="produto" data-produto-id="{{ $bebida->id }}" data-produto-nome="{{ $bebida->nome }}" data-produto-preco="{{ $bebida->preco }}">
                <div class="container-img">
                    <img src="{{ asset('img/produtos/' . $bebida->imagem_url) }}" alt="">
                </div>
                <div class="preco-conteiner">
                    <label class="lanche">{{ $bebida->nome }}</label>
                    <label class="preco">Preço</label>
                    <br>
                    <label class="valor">R${{ $bebida->preco }}</label>
                    <br>
                </div>
                @if(!empty($bebida->descricao))
                <div class="ingredientes-wrap">
                    <span class="ingredientes">ingredientes:</span>
                    <div class="ingredientes-lista">{{ $bebida->descricao }}</div>
                </div>
                @else
                <br>
                @endif
                <div>
                    <button type="button" class="button-adicionar">Adicionar ao carrinho</button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Modal: Adicionar ao carrinho -->
        <div class="modal fade " id="addToCartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content conteiner-info">
                    <form method="POST" action="{{ route('carrinho.adicionar') }}">
                        <div class="modal-header">
                            <h5 class="modal-title text_modal">Adicionar ao carrinho</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="cart_produto_id" name="produto_id" value="">
                            <input type="hidden" id="cart_preco" name="preco" value="">

                            <div class="mb-3">
                                <label class="form-label text_modal">Produto</label>
                                <input type="text" class="form-control" id="cart_produto_nome" value="" readonly disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text_modal" for="cart_quantidade">Quantidade</label>
                                <input type="number" class="form-control" id="cart_quantidade" name="quantidade" min="1" value="1" required>
                            </div>

                            <div class="mb-0 ">
                                <label class="form-label text_modal" for="cart_observacao">Observação (opcional)</label>
                                <textarea class="form-control" id="cart_observacao" name="observacao" rows="3" placeholder="Ex: sem cebola, bem passado..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancelar " data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-confirmar">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
        </div>
    </div>

</body>

</html>

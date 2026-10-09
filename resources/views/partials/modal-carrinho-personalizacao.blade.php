<div class="modal fade" id="addToCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content conteiner-info">
            <form method="POST" action="{{ route('carrinho.adicionar') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text_modal">Personalizar pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cart_produto_id" name="produto_id" value="">
                    <input type="hidden" id="cart_personalizacao_tipo" name="personalizacao[tipo]" value="">

                    <div class="mb-3">
                        <label class="form-label text_modal" for="cart_produto_nome">Produto</label>
                        <input type="text" class="form-control" id="cart_produto_nome" value="" readonly disabled>
                    </div>

                    <div class="mb-3" id="cartPizzaOptions" hidden>
                        <select id="cart_pizza_sabor_1" hidden aria-hidden="true" tabindex="-1" style="display:none !important">
                            @foreach(($pizzas ?? collect()) as $pizzaOpcao)
                                <option value="{{ $pizzaOpcao->id }}" data-nome="{{ $pizzaOpcao->nome }}" data-preco="{{ $pizzaOpcao->preco }}" data-precos='{{ json_encode($pizzaOpcao->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) }}' data-bordas='{{ json_encode($pizzaOpcao->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>{{ $pizzaOpcao->nome }}</option>
                            @endforeach
                        </select>

                        <label class="form-label text_modal" for="cart_pizza_modo">Como deseja a pizza?</label>
                        <select class="form-select mb-3" id="cart_pizza_modo" name="personalizacao[modo]" disabled>
                            <option value="inteira">Pizza inteira, um sabor</option>
                            <option value="meia_a_meia">Meia a meia, combine dois sabores</option>
                        </select>

                        <div class="mb-3" id="cartPizzaSecondFlavor" hidden>
                            <label class="form-label text_modal" for="cart_pizza_sabor_2">Segundo sabor (opcional)</label>
                            <small class="form-text">Escolha outro sabor para dividir a pizza ao meio.</small>
                            <select class="form-select" id="cart_pizza_sabor_2" name="personalizacao[sabor_2_id]" disabled>
                                <option value="" selected>Selecione outro sabor</option>
                                @foreach(($pizzas ?? collect()) as $pizzaOpcao)
                                    <option value="{{ $pizzaOpcao->id }}" data-nome="{{ $pizzaOpcao->nome }}" data-preco="{{ $pizzaOpcao->preco }}" data-precos='{{ json_encode($pizzaOpcao->precos_por_tamanho ?: \App\Services\PersonalizacaoProdutoService::PRECOS_PADRAO) }}' data-bordas='{{ json_encode($pizzaOpcao->bordas->where("ativa", true)->map(fn ($borda) => ["id" => $borda->id, "nome" => $borda->nome, "adicionais_por_tamanho" => $borda->adicionais_por_tamanho ?: []])->values()) }}'>{{ $pizzaOpcao->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text_modal" for="cart_pizza_borda">Qual borda deseja?</label>
                            <select class="form-select" id="cart_pizza_borda" name="personalizacao[borda_id]" disabled required>
                                <option value="">Escolha uma opção</option>
                                <option value="0">Sem borda</option>
                            </select>
                        </div>
                        <div class="mb-3" id="cartPizzaSizeField" hidden>
                            <label class="form-label text_modal" for="cart_pizza_tamanho">Tamanho e preço</label>
                            <select class="form-select" id="cart_pizza_tamanho" name="personalizacao[tamanho]" disabled required>
                                <option value="">Selecione o tamanho</option>
                            </select>
                        </div>
                        <small class="text-danger" id="cartPizzaSizeUnavailable" hidden>Os dois sabores precisam ter o mesmo tamanho cadastrado para serem combinados.</small>
                        <small class="form-text" id="cartPizzaPricePreview"></small>
                    </div>

                    <div class="mb-3" id="cartPortionOptions" hidden>
                        <label class="form-label text_modal" for="cart_tipo_porcao">Porção</label>
                        <select class="form-select" id="cart_tipo_porcao" name="personalizacao[tipo_porcao]" disabled>
                            <option value="inteira">Porção inteira</option>
                            <option value="meia">Meia porção</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text_modal" for="cart_quantidade">Quantidade</label>
                        <input type="number" class="form-control" id="cart_quantidade" name="quantidade" min="1" max="99" value="1" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label text_modal" for="cart_observacao">Observação (opcional)</label>
                        <textarea class="form-control" id="cart_observacao" name="observacao" rows="3" maxlength="500" placeholder="Ex: sem cebola, bem passado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-confirmar">Adicionar ao carrinho</button>
                </div>
            </form>
        </div>
    </div>
</div>

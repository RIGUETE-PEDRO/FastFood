<dialog class="pizza-prices-modal" id="pizzaPricesModal" aria-labelledby="pizzaPricesTitle">
    <div class="pizza-prices-modal__content">
        <header class="pizza-prices-modal__header">
            <div>
                <span class="pizza-prices-modal__eyebrow">Cardápio</span>
                <h2 id="pizzaPricesTitle">Tamanhos e preços</h2>
                <p>Valores por pizza inteira. A borda tradicional acrescenta R$ 7,00, exceto no XG, que acrescenta R$ 13,00.</p>
            </div>
            <button type="button" class="pizza-prices-modal__close" data-pizza-prices-close aria-label="Fechar preços">&times;</button>
        </header>

        <div class="pizza-prices-table" role="table" aria-label="Preços de pizza por tamanho">
            <div class="pizza-prices-table__row pizza-prices-table__row--heading" role="row">
                <span role="columnheader">Tamanho</span>
                <span role="columnheader">Sem borda</span>
                <span role="columnheader">Com borda</span>
            </div>
            @foreach([
                ['tamanho' => 'P', 'fatias' => 4, 'sem' => 56, 'com' => 63],
                ['tamanho' => 'M', 'fatias' => 6, 'sem' => 66, 'com' => 73],
                ['tamanho' => 'G', 'fatias' => 8, 'sem' => 76, 'com' => 83],
                ['tamanho' => 'GG', 'fatias' => 10, 'sem' => 86, 'com' => 93],
                ['tamanho' => 'XG', 'fatias' => 12, 'sem' => 100, 'com' => 113],
            ] as $preco)
                <div class="pizza-prices-table__row" role="row">
                    <span class="pizza-prices-table__size" role="cell"><strong>{{ $preco['tamanho'] }}</strong><small>{{ $preco['fatias'] }} fatias</small></span>
                    <strong role="cell">R$ {{ number_format($preco['sem'], 2, ',', '.') }}</strong>
                    <strong role="cell">R$ {{ number_format($preco['com'], 2, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>

        <p class="pizza-prices-modal__note">O preço final aparece ao escolher o sabor, o tamanho e a borda no pedido.</p>
        <button type="button" class="pizza-prices-modal__done" data-pizza-prices-close>Entendi</button>
    </div>
</dialog>

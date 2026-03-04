<section class="resumo-pedidos mb-4">
    @foreach($dashboardCards as $card)
        <article class="card-resumo {{ $card['accent'] }}">
            <span class="card-resumo__rotulo">{{ $card['label'] }}</span>
            <strong class="card-resumo__valor">{{ $card['valor'] }}</strong>
        </article>
    @endforeach
</section>

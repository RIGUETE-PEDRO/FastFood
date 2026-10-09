<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>Gerenciamento de bordas</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/GerenciamentoProduto.css') }}">
    <link rel="stylesheet" href="{{ asset('css/PersonalizacaoProduto.css') }}?v={{ filemtime(public_path('css/PersonalizacaoProduto.css')) }}">
</head>
<body>
    <div class="ff-shell">
        @include('layouts.sidebar')
        <div class="ff-main">
            <button type="button" class="ff-sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu">☰ Menu</button>
            <main class="gerenciamento-container bordas-admin">
                <div class="header-section">
                    <div class="bordas-admin__intro">
                        <span class="bordas-admin__eyebrow">Cardápio · Personalização</span>
                        <h1 class="page-title">Bordas de pizza</h1>
                        <p>Cadastre as opções e defina quanto cada borda acrescenta ao preço da pizza por tamanho.</p>
                    </div>
                    <a class="btn-add" href="{{ route('gerenciamento_Produtos') }}">Voltar aos produtos</a>
                </div>

                <section class="table-card bordas-admin__novo">
                    <div class="bordas-admin__section-heading">
                        <span class="bordas-admin__icon" aria-hidden="true">+</span>
                        <div><h2>Cadastrar borda</h2><p>Crie uma opção e informe os valores adicionais em reais.</p></div>
                    </div>
                    <form method="POST" action="{{ route('bordas.cadastrar') }}" class="borda-form">
                        @csrf
                        <div class="borda-form__name">
                            <label for="nova-borda-nome">Nome da borda</label>
                            <input id="nova-borda-nome" name="nome" maxlength="100" required value="{{ old('nome') }}" placeholder="Ex.: Catupiry">
                        </div>
                        <div class="borda-form__prices-section">
                            <div class="borda-form__prices-heading"><h3>Valor adicional por tamanho</h3><p>Este valor será somado ao preço da pizza. Use R$ 0,00 se não houver adicional.</p></div>
                            <div class="borda-form__prices">
                                @foreach(['P' => 4, 'M' => 6, 'G' => 8, 'GG' => 10, 'XG' => 12] as $tamanho => $fatias)
                                    <label class="borda-price-field" for="nova-borda-{{ $tamanho }}">
                                        <span class="borda-price-field__title"><strong>{{ $tamanho }}</strong><small>{{ $fatias }} fatias</small></span>
                                        <span class="borda-price-field__input"><span>R$</span><input id="nova-borda-{{ $tamanho }}" type="number" inputmode="decimal" aria-label="Valor adicional da borda para tamanho {{ $tamanho }}, em reais" name="adicionais_por_tamanho[{{ $tamanho }}]" min="0" step="0.01" placeholder="0,00" value="{{ old('adicionais_por_tamanho.' . $tamanho, '0') }}"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="borda-form__footer">
                            <label class="borda-form__active"><input type="checkbox" name="ativa" value="1" checked> <span><strong>Disponível para pedidos</strong><small>Clientes poderão selecionar esta borda.</small></span></label>
                            <button class="btn-add" type="submit">Cadastrar borda</button>
                        </div>
                    </form>
                </section>

                <section class="table-card bordas-admin__lista">
                    <div class="table-header bordas-admin__list-header">
                        <div><h2>Bordas cadastradas</h2><p>Edite os valores ou pause uma opção sem removê-la.</p></div>
                        <span class="bordas-admin__count">{{ $bordas->count() }} {{ $bordas->count() === 1 ? 'opção' : 'opções' }}</span>
                    </div>
                    <div class="borda-list">
                        @forelse($bordas as $borda)
                            <form method="POST" action="{{ route('bordas.atualizar', $borda->id) }}" class="borda-form borda-form--edit">
                                @csrf
                                @method('PUT')
                                <div class="borda-form__edit-heading">
                                    <div class="bordas-admin__section-heading">
                                        <span class="bordas-admin__icon bordas-admin__icon--money" aria-hidden="true">R$</span>
                                        <div><h3>Configurar borda</h3><p>Atualize o nome e os adicionais por tamanho.</p></div>
                                    </div>
                                    <span class="borda-status {{ $borda->ativa ? 'borda-status--active' : 'borda-status--inactive' }}">{{ $borda->ativa ? 'Ativa' : 'Pausada' }}</span>
                                </div>
                                <div class="borda-form__name">
                                    <label for="borda-nome-{{ $borda->id }}">Nome da borda @if($borda->padrao)<small>· padrão do sistema</small>@endif</label>
                                    <input id="borda-nome-{{ $borda->id }}" name="nome" maxlength="100" required value="{{ $borda->nome }}">
                                </div>
                                <div class="borda-form__prices-section">
                                    <div class="borda-form__prices-heading"><h3>Valor adicional por tamanho</h3><p>Valor em reais somado ao preço da pizza.</p></div>
                                    <div class="borda-form__prices">
                                        @foreach(['P' => 4, 'M' => 6, 'G' => 8, 'GG' => 10, 'XG' => 12] as $tamanho => $fatias)
                                            <label class="borda-price-field" for="borda-{{ $borda->id }}-{{ $tamanho }}">
                                                <span class="borda-price-field__title"><strong>{{ $tamanho }}</strong><small>{{ $fatias }} fatias</small></span>
                                                <span class="borda-price-field__input"><span>R$</span><input id="borda-{{ $borda->id }}-{{ $tamanho }}" type="number" inputmode="decimal" aria-label="Valor adicional da borda para tamanho {{ $tamanho }}, em reais" name="adicionais_por_tamanho[{{ $tamanho }}]" min="0" step="0.01" placeholder="0,00" value="{{ data_get($borda->adicionais_por_tamanho, $tamanho, '0') }}"></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="borda-form__footer">
                                    <label class="borda-form__active"><input type="checkbox" name="ativa" value="1" @checked($borda->ativa)> <span><strong>Disponível para pedidos</strong><small>Desmarque para pausar sem apagar a borda.</small></span></label>
                                    <button class="btn-add" type="submit">Salvar alterações</button>
                                </div>
                            </form>
                        @empty
                            <div class="borda-empty"><span aria-hidden="true">+</span><strong>Nenhuma borda cadastrada</strong><p>Use o formulário acima para adicionar a primeira opção.</p></div>
                        @endforelse
                    </div>
                </section>
                <p class="borda-form__hint"><strong>Próximo passo:</strong> abra o cadastro do produto e marque quais pizzas oferecem cada borda.</p>
            </main>
            @include('components.flash-toast')
        </div>
    </div>
</body>
</html>

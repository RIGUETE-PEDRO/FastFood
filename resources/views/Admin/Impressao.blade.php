<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>Impressão | Admin</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/Admin/Principal.css') }}?v={{ filemtime(public_path('css/Admin/Principal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Impressao.css') }}?v={{ filemtime(public_path('css/Admin/Impressao.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/Admin/Notinha.css') }}?v={{ filemtime(public_path('css/Admin/Notinha.css')) }}">
</head>
<body>
        <main class="print-settings">
            <a class="print-back" href="{{ $impressaoReturnUrl }}">&larr; Voltar</a>
            <header class="print-hero">
                <span>Preferências</span>
                <h1>Configuração de impressão</h1>
                <p>Ajuste o formato dos cupons para a impressora térmica usada neste navegador.</p>
            </header>
            <section class="print-layout">
                <form id="print-settings-form" class="print-card" action="{{ route('admin.impressao.atualizar') }}" method="POST">
                    @csrf
                    <input type="hidden" name="return" value="{{ $impressaoReturnUrl }}">
                    <h2>Formato e conteúdo</h2>
                    <label class="print-field">Largura do papel
                        <select name="paperWidth"><option value="58">Térmica 58 mm</option><option value="80">Térmica 80 mm</option></select>
                    </label>
                    <label class="print-field">Tamanho do texto
                        <select name="fontSize"><option value="10">10 px</option><option value="12">12 px</option><option value="14">14 px</option></select>
                    </label>
                    <label class="print-field">Cópias por cupom
                        <select name="copies"><option value="1">1 cópia</option><option value="2">2 cópias</option><option value="3">3 cópias</option><option value="4">4 cópias</option><option value="5">5 cópias</option></select>
                    </label>
                    <label class="print-field">Margem interna
                        <select name="padding"><option value="compact">Compacta</option><option value="normal">Padrão</option></select>
                    </label>
                    <label class="print-check">
                        <input type="hidden" name="autoPrintOnAccept" value="false">
                        <input type="checkbox" name="autoPrintOnAccept" value="true" @checked($configuracoesImpressao['autoPrintOnAccept'] ?? false)>
                        <span>Imprimir cupom automaticamente ao aceitar pedido</span>
                    </label>
                    <div class="print-typography" aria-labelledby="receipt-font-title">
                        <h2 id="receipt-font-title">Fonte da nota</h2>
                        <p>A prévia do cupom acompanha a fonte escolhida. Clique em salvar para manter a preferência.</p>
                        <label class="print-field">Fonte da nota
                            <select name="receiptFont" aria-label="Fonte da nota">
                                <option value="monospace" @selected(($configuracoesImpressao['receiptFont'] ?? 'monospace') === 'monospace')>Monoespaçada (Courier New)</option>
                                <option value="arial" @selected(($configuracoesImpressao['receiptFont'] ?? 'monospace') === 'arial')>Arial</option>
                                <option value="georgia" @selected(($configuracoesImpressao['receiptFont'] ?? 'monospace') === 'georgia')>Georgia</option>
                            </select>
                        </label>
                        <span class="print-typography__status" data-receipt-font-status role="status" aria-live="polite"></span>
                    </div>
                    <p class="print-note">A impressão também depende do driver e do modelo da impressora.</p>
                    <button type="submit" class="print-save">Salvar preferências</button>
                    <p id="print-saved" class="print-saved" role="status" aria-live="polite"></p>
                </form>
                <aside class="print-card print-example">
                    <h2>Pré-visualização</h2>
                    <div class="paper-preview" id="paper-preview">
                        <section class="cupom">
                            <header class="receipt-header"><strong class="receipt-store">FlashFood</strong><span>Rua das Flores, 123</span><span>Centro - São Paulo/SP</span><span>Tel: (11) 99999-0000</span></header>
                            <div class="receipt-separator"></div>
                            <section class="receipt-block"><div class="receipt-row"><span>Cupom</span><strong>#000123</strong></div><div class="receipt-row"><span>Data</span><span>03/10/2026</span></div><div class="receipt-row"><span>Hora</span><span>12:30</span></div><div class="receipt-row"><span>Cliente</span><strong>Cliente Exemplo</strong></div><div class="receipt-row"><span>Tipo</span><strong class="receipt-pill">Mesa</strong></div><div class="receipt-row"><span>Mesa</span><span>Mesa 2</span></div></section>
                            <div class="receipt-separator"></div>
                            <section class="receipt-block"><h2 class="receipt-title">Itens do pedido</h2><div class="receipt-item"><div class="receipt-item__line"><strong>2x Hambúrguer</strong><small>Obs.: sem cebola</small></div><div class="receipt-row receipt-row--small"><span>Unit. R$ 20,00</span><span>R$ 40,00</span></div></div><div class="receipt-item"><div class="receipt-item__line"><strong>1x Refrigerante</strong></div><div class="receipt-row receipt-row--small"><span>Unit. R$ 5,00</span><span>R$ 5,00</span></div></div></section>
                            <div class="receipt-separator"></div>
                            <section class="receipt-block"><div class="receipt-row"><span>Qtd. itens</span><span>3</span></div><div class="receipt-row"><span>Subtotal</span><span>R$ 45,00</span></div><div class="receipt-row"><span>Pagamento</span><span>Pix</span></div><div class="receipt-total"><span>Total</span><strong>R$ 45,00</strong></div></section>
                            <div class="receipt-separator"></div><footer class="receipt-footer"><strong>Obrigado pela preferência!</strong><span>Este cupom não tem validade fiscal.</span><span>Guarde para eventuais conferências.</span></footer>
                        </section>
                    </div>
                </aside>
            </section>
        </main>
<script>window.printSettings = @json($configuracoesImpressao);</script>
<script src="{{ asset('js/admin-impressao.js') }}?v={{ filemtime(public_path('js/admin-impressao.js')) }}" defer></script>
@include('components.flash-toast')
</body>
</html>

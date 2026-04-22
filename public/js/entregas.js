document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const categoriaSelect = document.getElementById('categoria_id');
    const filtroForm = document.getElementById('entregas-filter-form');
    const limparFiltroBtn = document.getElementById('limpar-filtro');

    if (!tableBody) return;

    const rows = Array.from(tableBody.querySelectorAll('tr'));

    function aplicarFiltros() {
        const termoBusca = (searchInput?.value || '').trim().toLowerCase();
        const categoriaSelecionadaId = (categoriaSelect?.value || '').trim();
        const categoriaSelecionadaNome = (categoriaSelect?.selectedOptions?.[0]?.textContent || '').trim().toLowerCase();

        rows.forEach(function (row) {
            const nome = row.querySelector('.nome-cell')?.textContent?.toLowerCase() || '';
            const categoria = row.children[3]?.textContent?.toLowerCase() || '';

            const passouBusca = !termoBusca || `${nome} ${categoria}`.includes(termoBusca);
            const passouCategoria = !categoriaSelecionadaId || categoria === categoriaSelecionadaNome;

            row.style.display = passouBusca && passouCategoria ? '' : 'none';
        });
    }

    filtroForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        aplicarFiltros();
    });

    categoriaSelect?.addEventListener('change', aplicarFiltros);
    searchInput?.addEventListener('input', aplicarFiltros);

    limparFiltroBtn?.addEventListener('click', function () {
        if (categoriaSelect) {
            categoriaSelect.value = '';
        }
        if (searchInput) {
            searchInput.value = '';
        }
        aplicarFiltros();
    });
});

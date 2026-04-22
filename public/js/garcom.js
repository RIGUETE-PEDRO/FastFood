document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const mesaSelect = document.getElementById('mesa_id');
    const formsAdicionar = document.querySelectorAll('.add-produto-form');
    const categoriaSelect = document.getElementById('categoria_id');
    const filtroForm = document.querySelector('.garcom-filter-form');
    const limparFiltroBtn = document.querySelector('.garcom-filter-clear');

    function garantirEstiloPopup() {
        if (document.getElementById('garcom-popup-style')) return;

        const style = document.createElement('style');
        style.id = 'garcom-popup-style';
        style.textContent = `
            .garcom-popup-stack {
                position: fixed;
                bottom: 16px;
                right: 16px;
                z-index: 4000;
                display: grid;
                gap: 10px;
                max-width: min(92vw, 420px);
            }

            .garcom-popup {
                display: grid;
                grid-template-columns: 1fr auto;
                align-items: start;
                gap: 10px;
                padding: 12px;
                border-radius: 12px;
                color: #fff;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
                border: 1px solid rgba(255, 255, 255, .22);
                background: linear-gradient(135deg, #f59e0b, #d97706);
                animation: garcomPopupIn .2s ease-out;
            }

            .garcom-popup__close {
                border: none;
                background: rgba(255,255,255,.2);
                color: #fff;
                width: 26px;
                height: 26px;
                border-radius: 999px;
                cursor: pointer;
                font-size: 18px;
                line-height: 1;
                padding: 0;
            }

            .garcom-popup--hide {
                opacity: 0;
                transform: translateY(6px);
                transition: all .18s ease;
            }

            @keyframes garcomPopupIn {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;

        document.head.appendChild(style);
    }

    function mostrarPopup(mensagem) {
        garantirEstiloPopup();

        let stack = document.getElementById('garcom-popup-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'garcom-popup-stack';
            stack.className = 'garcom-popup-stack';
            document.body.appendChild(stack);
        }

        const popup = document.createElement('div');
        popup.className = 'garcom-popup';
        popup.innerHTML = `
            <div>${mensagem}</div>
            <button type="button" class="garcom-popup__close" aria-label="Fechar">×</button>
        `;

        const fechar = () => {
            popup.classList.add('garcom-popup--hide');
            setTimeout(() => popup.remove(), 180);
        };

        popup.querySelector('.garcom-popup__close')?.addEventListener('click', fechar);
        stack.appendChild(popup);
        setTimeout(fechar, 2800);
    }

    function logEPopup(mensagem) {
        console.log(mensagem);
        mostrarPopup(mensagem);
    }

    formsAdicionar.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const mesaId = mesaSelect?.value;

            if (!mesaId) {
                event.preventDefault();
                logEPopup('Selecione uma mesa antes de adicionar o produto.');
                mesaSelect?.focus();
                return;
            }

            const hiddenMesa = form.querySelector('.mesa-id-hidden');
            if (hiddenMesa) {
                hiddenMesa.value = mesaId;
            }
        });
    });

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

    limparFiltroBtn?.addEventListener('click', function () {
        if (categoriaSelect) {
            categoriaSelect.value = '';
        }
        if (searchInput) {
            searchInput.value = '';
        }
        aplicarFiltros();
    });

    searchInput?.addEventListener('input', aplicarFiltros);
});

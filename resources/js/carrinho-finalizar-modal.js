// Modal de finalizar compra (retirar no local vs entrega)
// Regras:
// - Se "retirar" estiver selecionado: pedir número da mesa (obrigatório)
// - Se "entrega" estiver selecionado: pedir endereço (obrigatório)
// - Ao confirmar: envia form com campos `tipo_entrega`, `mesa` e/ou `endereco`

(function () {
    const tipoModal = document.getElementById('finalizarModal');
    const mesaModal = document.getElementById('mesaModal');
    const enderecoModal = document.getElementById('enderecoModal');
    if (!tipoModal || !mesaModal || !enderecoModal) return;

    const btnAbrir = document.getElementById('btnFinalizarCompra');

    const tipoForm = document.getElementById('tipoEntregaForm');
    const tipoErro = document.getElementById('tipoEntregaErro');

    const mesaForm = document.getElementById('mesaForm');
    const mesaInput = document.getElementById('mesa');
    const mesaErro = document.getElementById('mesaErro');

    const enderecoForm = document.getElementById('enderecoForm');
    const enderecoInput = document.getElementById('endereco');
    const enderecoErro = document.getElementById('enderecoErro');

    function openModal(el) {
        el.classList.add('is-open');
        el.setAttribute('aria-hidden', 'false');
    }

    function closeModal(el) {
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
    }

    function closeAll() {
        closeModal(tipoModal);
        closeModal(mesaModal);
        closeModal(enderecoModal);
        if (tipoErro) tipoErro.textContent = '';
        if (mesaErro) mesaErro.textContent = '';
        if (enderecoErro) enderecoErro.textContent = '';
    }

    function abrir() {
        closeAll();
        openModal(tipoModal);
        const primeiro = tipoModal.querySelector('input[name="tipo_entrega"]');
        if (primeiro) primeiro.focus();
    }

    function tipoAtual() {
        const checked = tipoModal.querySelector('input[name="tipo_entrega"]:checked');
        return checked ? checked.value : null;
    }

    if (btnAbrir) {
        btnAbrir.addEventListener('click', (e) => {
            e.preventDefault();
            abrir();
        });
    }

    // Fechar (X/overlay/cancelar) em qualquer modal
    [tipoModal, mesaModal, enderecoModal].forEach((m) => {
        m.querySelectorAll('[data-modal-close]').forEach((b) => b.addEventListener('click', closeAll));
        const ov = m.querySelector('.ff-modal__overlay');
        if (ov) ov.addEventListener('click', closeAll);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && (tipoModal.classList.contains('is-open') || mesaModal.classList.contains('is-open') || enderecoModal.classList.contains('is-open'))) {
            closeAll();
        }
    });

    // Voltar do modal 2 para modal 1
    [mesaModal, enderecoModal].forEach((m) => {
        m.querySelectorAll('[data-modal-back]').forEach((b) =>
            b.addEventListener('click', () => {
                closeModal(m);
                openModal(tipoModal);
            })
        );
    });

    // Modal 1 -> abre modal 2A ou 2B
    if (tipoForm) {
        tipoForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const tipo = tipoAtual();
            if (!tipo) {
                if (tipoErro) tipoErro.textContent = 'Selecione uma opção para continuar.';
                return;
            }
            if (tipoErro) tipoErro.textContent = '';

            if (tipo === 'retirar') {
                closeModal(tipoModal);
                openModal(mesaModal);
                if (mesaInput) mesaInput.focus();
            } else {
                closeModal(tipoModal);
                openModal(enderecoModal);
                if (enderecoInput) enderecoInput.focus();
            }
        });
    }

    // Modal mesa: valida e envia
    if (mesaForm) {
        mesaForm.addEventListener('submit', (e) => {
            const mesa = (mesaInput?.value || '').trim();
            if (!mesa) {
                e.preventDefault();
                if (mesaErro) mesaErro.textContent = 'Digite o número da mesa.';
                mesaInput?.focus();
                return;
            }
            if (mesaErro) mesaErro.textContent = '';
            closeAll();
        });
    }

    // Modal endereço: valida e envia
    if (enderecoForm) {
        enderecoForm.addEventListener('submit', (e) => {
            const end = (enderecoInput?.value || '').trim();
            if (!end) {
                e.preventDefault();
                if (enderecoErro) enderecoErro.textContent = 'Digite o endereço de entrega.';
                enderecoInput?.focus();
                return;
            }
            if (enderecoErro) enderecoErro.textContent = '';
            closeAll();
        });
    }
})();

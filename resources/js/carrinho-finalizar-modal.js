// Modal de finalizar compra (retirar no local vs entrega)
// Regras:
// - Se "retirar" estiver selecionado: pedir número da mesa (obrigatório)
// - Se "entrega" estiver selecionado: pedir endereço (obrigatório)
// - Ao confirmar: envia form com campos `tipo_entrega`, `mesa` e/ou `endereco`

(function () {
    const tipoModal = document.getElementById('finalizarModal');
    const mesaModal = document.getElementById('mesaModal');
    const enderecoModal = document.getElementById('enderecoModal');
    const enderecoNovoModal = document.getElementById('enderecoNovoModal');
    const pagamentoModal = document.getElementById('pagamentoModal');

    if (!tipoModal || !mesaModal || !enderecoModal || !enderecoNovoModal || !pagamentoModal) return;

    const modals = [tipoModal, mesaModal, enderecoModal, enderecoNovoModal, pagamentoModal];

    const btnAbrir = document.getElementById('btnFinalizarCompra');

    const tipoForm = document.getElementById('tipoEntregaForm');
    const tipoErro = document.getElementById('tipoEntregaErro');

    const mesaForm = document.getElementById('mesaForm');
    const mesaInput = document.getElementById('mesa');
    const mesaErro = document.getElementById('mesaErro');

    const enderecoForm = document.getElementById('enderecoForm');
    const enderecoSelecionadoErro = document.getElementById('enderecoSelecionadoErro');

    const enderecoNovoForm = document.getElementById('enderecoNovoForm');
    const enderecoNovoErro = document.getElementById('enderecoNovoErro');
    const enderecoNovoCampos = {
        bairro: document.getElementById('novo_bairro'),
        rua: document.getElementById('novo_rua'),
        numero: document.getElementById('novo_numero'),
        complemento: document.getElementById('novo_complemento'),
    };

    function radiosEnderecos() {
        return enderecoForm ? [...enderecoForm.querySelectorAll('input[name="endereco_opcao"]')] : [];
    }

    function temEnderecosSalvos() {
        return radiosEnderecos().length > 0;
    }

    function focusPrimeiroEndereco() {
        const primeiro = radiosEnderecos()[0];
        if (primeiro) primeiro.focus();
    }

    function focusNovoEndereco() {
        if (enderecoNovoCampos.bairro) {
            enderecoNovoCampos.bairro.focus();
        }
    }

    function focusPagamento() {
        const primeiro = pagamentoModal.querySelector('input[name="pagamento_metodo"]');
        if (primeiro) primeiro.focus();
    }

    function openModal(el) {
        el.classList.add('is-open');
        el.setAttribute('aria-hidden', 'false');
    }

    function closeModal(el) {
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
    }

    function algumModalAberto() {
        return modals.some((m) => m.classList.contains('is-open'));
    }

    function limparErros() {
        if (tipoErro) tipoErro.textContent = '';
        if (mesaErro) mesaErro.textContent = '';
        if (enderecoSelecionadoErro) enderecoSelecionadoErro.textContent = '';
        if (enderecoNovoErro) enderecoNovoErro.textContent = '';
    }

    function closeAll() {
        modals.forEach(closeModal);
        limparErros();
    }

    function abrirFluxo() {
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
            abrirFluxo();
        });
    }

    modals.forEach((m) => {
        m.querySelectorAll('[data-modal-close]').forEach((btn) => {
            btn.addEventListener('click', closeAll);
        });

        const overlay = m.querySelector('.ff-modal__overlay');
        if (overlay) {
            overlay.addEventListener('click', closeAll);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && algumModalAberto()) {
            closeAll();
        }
    });

    document.querySelectorAll('[data-modal-back]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.modalBack || 'finalizarModal';
            const current = btn.closest('.ff-modal');
            if (current) closeModal(current);
            const targetModal = document.getElementById(targetId);
            if (targetModal) {
                openModal(targetModal);
                if (targetModal === tipoModal) {
                    const primeiro = tipoModal.querySelector('input[name="tipo_entrega"]');
                    if (primeiro) primeiro.focus();
                } else if (targetModal === enderecoModal) {
                    focusPrimeiroEndereco();
                } else if (targetModal === enderecoNovoModal) {
                    focusNovoEndereco();
                } else if (targetModal === pagamentoModal) {
                    focusPagamento();
                }
            }
        });
    });

    document.querySelectorAll('[data-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.modalOpen;
            if (!targetId) return;
            const targetModal = document.getElementById(targetId);
            if (!targetModal) return;
            const current = btn.closest('.ff-modal');
            if (current) closeModal(current);
            openModal(targetModal);
            if (targetModal === enderecoNovoModal) {
                focusNovoEndereco();
            } else if (targetModal === enderecoModal) {
                focusPrimeiroEndereco();
            } else if (targetModal === pagamentoModal) {
                focusPagamento();
            }
        });
    });

    if (tipoForm) {
        tipoForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const tipo = tipoAtual();
            if (!tipo) {
                if (tipoErro) tipoErro.textContent = 'Selecione uma opção para continuar.';
                return;
            }
            if (tipoErro) tipoErro.textContent = '';

            closeModal(tipoModal);

            if (tipo === 'retirar') {
                openModal(mesaModal);
                if (mesaInput) mesaInput.focus();
            } else if (temEnderecosSalvos()) {
                openModal(enderecoModal);
                focusPrimeiroEndereco();
            } else {
                openModal(enderecoNovoModal);
                focusNovoEndereco();
            }
        });
    }

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

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const suportaAjaxEndereco = typeof window.fetch === 'function' && Boolean(csrfToken);

    if (enderecoForm) {
        const enderecoSubmitBtn = enderecoForm.querySelector('button[type="submit"]');

        enderecoForm.addEventListener('submit', async (e) => {
            const selecionado = enderecoForm.querySelector('input[name="endereco_opcao"]:checked');
            if (!selecionado) {
                e.preventDefault();
                if (temEnderecosSalvos()) {
                    if (enderecoSelecionadoErro) {
                        enderecoSelecionadoErro.textContent = 'Selecione um endereço para continuar.';
                    }
                    focusPrimeiroEndereco();
                } else {
                    openModal(enderecoNovoModal);
                    focusNovoEndereco();
                }
                return;
            }

            if (enderecoSelecionadoErro) enderecoSelecionadoErro.textContent = '';

            if (!suportaAjaxEndereco) {
                return;
            }

            e.preventDefault();

            const textoOriginal = enderecoSubmitBtn?.textContent;
            if (enderecoSubmitBtn) {
                enderecoSubmitBtn.disabled = true;
                enderecoSubmitBtn.textContent = 'Confirmando...';
            }

            try {
                const resposta = await fetch(enderecoForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: new FormData(enderecoForm),
                });

                const payload = await resposta.json().catch(() => ({}));

                if (!resposta.ok || !payload?.status) {
                    if (enderecoSelecionadoErro) {
                        enderecoSelecionadoErro.textContent = payload?.mensagem || 'Não foi possível confirmar o endereço.';
                    }
                    openModal(enderecoModal);
                    focusPrimeiroEndereco();
                    return;
                }

                closeAll();
                openModal(pagamentoModal);
                focusPagamento();
            } catch (error) {
                if (enderecoSelecionadoErro) {
                    enderecoSelecionadoErro.textContent = 'Não foi possível confirmar o endereço. Tente novamente.';
                }
                openModal(enderecoModal);
                focusPrimeiroEndereco();
            } finally {
                if (enderecoSubmitBtn) {
                    enderecoSubmitBtn.disabled = false;
                    enderecoSubmitBtn.textContent = textoOriginal || 'Usar endereço selecionado';
                }
            }
        });
    }

    if (enderecoNovoForm) {
        enderecoNovoForm.addEventListener('submit', (e) => {
            const obrigatorios = [
                { chave: 'bairro', rotulo: 'Bairro' },
                { chave: 'rua', rotulo: 'Rua' },
            ];

            const faltando = obrigatorios.filter(({ chave }) => {
                const campo = enderecoNovoCampos[chave];
                if (!campo) return true;
                return !campo.value.trim();
            });

            if (faltando.length) {
                e.preventDefault();
                if (enderecoNovoErro) {
                    const etiquetas = faltando.map(({ rotulo }) => rotulo).join(' e ');
                    enderecoNovoErro.textContent = `Preencha os campos obrigatórios: ${etiquetas}.`;
                }

                const primeiro = faltando[0]?.chave;
                const campoFoco = primeiro ? enderecoNovoCampos[primeiro] : null;
                if (campoFoco) campoFoco.focus();
                return;
            }

            if (enderecoNovoErro) enderecoNovoErro.textContent = '';
            closeAll();
        });
    }

    const autoModalId = document.body?.dataset?.openModal;
    if (autoModalId) {
        const targetModal = document.getElementById(autoModalId);
        if (targetModal) {
            closeAll();

            if (autoModalId === 'enderecoModal' || autoModalId === 'enderecoNovoModal' || autoModalId === 'pagamentoModal') {
                const entregaRadio = tipoModal.querySelector('input[name="tipo_entrega"][value="entrega"]');
                if (entregaRadio) entregaRadio.checked = true;
            }

            openModal(targetModal);

            if (targetModal === enderecoModal) {
                focusPrimeiroEndereco();
            } else if (targetModal === enderecoNovoModal) {
                focusNovoEndereco();
            } else if (targetModal === mesaModal) {
                mesaInput?.focus();
            } else if (targetModal === tipoModal) {
                const primeiro = tipoModal.querySelector('input[name="tipo_entrega"]');
                if (primeiro) primeiro.focus();
            } else if (targetModal === pagamentoModal) {
                focusPagamento();
            }
        }
    }
})();

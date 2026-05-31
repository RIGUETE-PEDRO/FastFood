document.addEventListener('DOMContentLoaded', () => {
    const btnAtivo = document.getElementById('btnAtivo');
    const btnInativo = document.getElementById('btnInativo');
    const inputAtivo = document.getElementById('produto-ativo');

    function setStatus(ativo) {
        if (!btnAtivo || !btnInativo || !inputAtivo) return;

        btnAtivo.classList.toggle('active', ativo);
        btnInativo.classList.toggle('active', !ativo);
        inputAtivo.value = ativo ? '1' : '0';
    }

    btnAtivo?.addEventListener('click', () => setStatus(true));
    btnInativo?.addEventListener('click', () => setStatus(false));
    setStatus(true);

    const openBtn = document.getElementById('openCreateProduct');
    const closeBtn = document.getElementById('closeCreateProduct');
    const cancelBtn = document.getElementById('cancelCreateProduct');
    const overlay = document.getElementById('createProductOverlay');
    const form = document.getElementById('formCreateProduct');
    const imageInput = document.getElementById('produto-imagem');
    const imageName = document.getElementById('imagem-nome');

    const clearForm = () => {
        if (!form) return;

        form.reset();
        form.action = form.getAttribute('data-cadastro-action') || form.action;
        document.getElementById('produto_id').value = '';
        if (imageName) imageName.textContent = 'Nenhum selecionado';
        setStatus(true);
    };

    const setOverlayBodyState = () => {
        const isOpen = Boolean(document.querySelector('.overlay-backdrop.is-open'));
        document.body.classList.toggle('ff-modal-open', isOpen);
        if (window.ff?.syncSidebarLock) window.ff.syncSidebarLock();
    };

    const openOverlay = () => {
        if (window.ff?.closeSidebar) window.ff.closeSidebar();
        overlay?.classList.add('is-open');
        setOverlayBodyState();
    };

    const closeOverlay = () => {
        overlay?.classList.remove('is-open');
        setOverlayBodyState();
    };

    openBtn?.addEventListener('click', () => {
        clearForm();
        document.getElementById('overlay-badge').textContent = 'Novo produto';
        document.getElementById('overlay-title').textContent = 'Cadastrar produto';
        document.getElementById('overlay-subtitle').textContent = 'Preencha os dados para adicionar um produto ao cardapio.';
        openOverlay();
    });

    closeBtn?.addEventListener('click', closeOverlay);
    cancelBtn?.addEventListener('click', closeOverlay);
    overlay?.addEventListener('click', (e) => {
        if (e.target === overlay) closeOverlay();
    });

    document.querySelectorAll('.btn-edit').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            document.getElementById('overlay-badge').textContent = 'Atualizando produto';
            document.getElementById('overlay-title').textContent = 'Atualizando produto';
            document.getElementById('overlay-subtitle').textContent = 'Altere os dados e salve para atualizar o produto.';

            if (form) {
                form.action = btn.getAttribute('data-action') || form.action;
            }

            document.getElementById('produto_id').value = btn.closest('tr')?.querySelector('.btn-delete')?.getAttribute('data-produto-id') || '';
            document.getElementById('produto-nome').value = btn.getAttribute('data-nome') || '';
            document.getElementById('produto-preco').value = btn.getAttribute('data-preco') || '';
            document.getElementById('produto-descricao').value = btn.getAttribute('data-descricao') || '';
            document.getElementById('produto-categoria').value = btn.getAttribute('data-categoria-id') || '';
            setStatus(btn.getAttribute('data-ativo') === '1');

            if (imageName) {
                imageName.textContent = btn.getAttribute('data-imagem-url') || 'Nenhum selecionado';
            }
            openOverlay();
        });
    });

    imageInput?.addEventListener('change', () => {
        if (!imageName) return;
        imageName.textContent = imageInput.files?.[0]?.name || 'Nenhum selecionado';
    });

    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');

    searchInput?.addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase();
        const rows = tableBody?.querySelectorAll('tr') || [];

        rows.forEach((row) => {
            const nome = row.querySelector('.nome-cell')?.textContent?.toLowerCase() || '';
            row.style.display = nome.includes(searchValue) ? '' : 'none';
        });
    });

    const toggles = document.querySelectorAll('.toggle-carrousel');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    toggles.forEach((toggle) => {
        toggle.addEventListener('change', async function () {
            const produtoId = this.getAttribute('data-produto-id');
            const url = this.getAttribute('data-url') || `/api/produtos/${produtoId}/carrousel`;
            const nextValue = this.checked;
            const previousValue = !nextValue;

            this.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        no_carrousel: nextValue ? 1 : 0,
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                    this.checked = previousValue;
                    if (window.showToast) {
                        window.showToast(data.message || 'Erro ao atualizar carrousel', 'error');
                    }
                    return;
                }

                this.checked = Boolean(data.data?.no_carrousel);
                if (window.showToast) {
                    window.showToast(data.message, 'success');
                }
            } catch (error) {
                this.checked = previousValue;
                if (window.showToast) {
                    window.showToast('Erro ao atualizar carrousel', 'error');
                }
            } finally {
                this.disabled = false;
            }
        });
    });
});

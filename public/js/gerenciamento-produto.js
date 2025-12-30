    // Botão de status ativo/inativo
    const btnAtivo = document.getElementById('btnAtivo');
    const btnInativo = document.getElementById('btnInativo');
    const inputAtivo = document.getElementById('produto-ativo');

    function setStatus(ativo) {
        if (!btnAtivo || !btnInativo || !inputAtivo) return;
        if (ativo) {
            btnAtivo.classList.add('active');
            btnInativo.classList.remove('active');
        } else {
            btnAtivo.classList.remove('active');
            btnInativo.classList.add('active');
        }
        inputAtivo.value = ativo ? '1' : '0';
    }
    btnAtivo?.addEventListener('click', () => setStatus(true));
    btnInativo?.addEventListener('click', () => setStatus(false));
    setStatus(true);
document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('openCreateProduct');
    const closeBtn = document.getElementById('closeCreateProduct');
    const cancelBtn = document.getElementById('cancelCreateProduct');
    const overlay = document.getElementById('createProductOverlay');
    const form = document.getElementById('formCreateProduct');

    const clearForm = () => {
        if (!form) return;
        form.reset();
        form.action = '#';
        document.getElementById('produto_id').value = '';
    };

    const openOverlay = () => overlay?.classList.add('is-open');
    const closeOverlay = () => overlay?.classList.remove('is-open');

    openBtn?.addEventListener('click', () => {
        clearForm();
        openOverlay();
    });
    closeBtn?.addEventListener('click', closeOverlay);
    cancelBtn?.addEventListener('click', closeOverlay);
    overlay?.addEventListener('click', (e) => {
        if (e.target === overlay) closeOverlay();
    });

    // Exemplo: preencher para edição (você pode adaptar para backend)
    document.querySelectorAll('.btn-edit').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // Aqui você pode preencher os campos com os dados do produto
            // Exemplo:
            // document.getElementById('produto_id').value = btn.dataset.id;
            // document.getElementById('produto-nome').value = btn.dataset.nome;
            // ...
            openOverlay();
        });
    });

    // Busca em tempo real (filtro)
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    searchInput?.addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase();
        const rows = tableBody?.querySelectorAll('tr') || [];
        rows.forEach(row => {
            const nome = row.querySelector('.nome-cell')?.textContent?.toLowerCase() || '';
            row.style.display = nome.includes(searchValue) ? '' : 'none';
        });
    });
});

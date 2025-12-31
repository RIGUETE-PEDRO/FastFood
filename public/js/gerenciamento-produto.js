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
    // Modo cadastro
    document.getElementById('overlay-badge').textContent = 'Novo produto';
    document.getElementById('overlay-title').textContent = 'Cadastrar produto';
    document.getElementById('overlay-subtitle').textContent = 'Preencha os dados para adicionar um produto ao cardápio.';
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
            // Modo edição
            document.getElementById('overlay-badge').textContent = 'Atualizando produto';
            document.getElementById('overlay-title').textContent = 'Atualizando produto';
            document.getElementById('overlay-subtitle').textContent = 'Altere os dados e salve para atualizar o produto.';
            // Preencher os campos do formulário com os dados do produto
            document.getElementById('produto_id').value = btn.closest('tr').querySelector('.btn-delete').getAttribute('data-produto-id') || '';
            document.getElementById('produto-nome').value = btn.getAttribute('data-nome') || '';
            document.getElementById('produto-preco').value = btn.getAttribute('data-preco') || '';
            document.getElementById('produto-descricao').value = btn.getAttribute('data-descricao') || '';
            document.getElementById('produto-categoria').value = btn.getAttribute('data-categoria-id') || '';
            setStatus(btn.getAttribute('data-ativo') === '1');
            // Preencher o nome da imagem abaixo do input
            const imagemNome = document.getElementById('imagem-nome');
            const imagemUrl = btn.getAttribute('data-imagem-url');
            imagemNome.textContent = imagemUrl ? imagemUrl : '';
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

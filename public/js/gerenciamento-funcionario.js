document.addEventListener('DOMContentLoaded', () => {
    // Controle do overlay de cadastro/edição
    const openBtn = document.getElementById('openCreateUser');
    const closeBtn = document.getElementById('closeCreateUser');
    const cancelBtn = document.getElementById('cancelCreateUser');
    const overlay = document.getElementById('createUserOverlay');
    const form = document.getElementById('formCreateUser');

    const nomeInput = document.getElementById('nome');
    const emailInput = document.getElementById('email');
    const telefoneInput = document.getElementById('telefone');
    const tipoInput = document.getElementById('tipo_usuario_id');
    const statusInput = document.getElementById('has_ativo');
    const salarioInput = document.getElementById('salario');
    const senhaInput = document.getElementById('senha');
    const senhaConfirmInput = document.getElementById('senha_confirmation');
    const usuarioIdInput = document.getElementById('usuario_id');

    // Modal de confirmação de deleção
    const deleteOverlay = document.getElementById('deleteConfirmOverlay');
    const deleteForm = document.getElementById('deleteForm');
    const deleteConfirmText = document.getElementById('deleteConfirmText');
    const closeDeleteBtn = document.getElementById('closeDeleteConfirm');
    const cancelDeleteBtn = document.getElementById('cancelDelete');

    // grupos para mostrar/esconder campos de senha
    const senhaGroup = senhaInput?.closest('.form-group');
    const senhaConfirmGroup = senhaConfirmInput?.closest('.form-group');

    const defaultAction = form?.dataset?.defaultAction || '';

    const openOverlay = () => overlay?.classList.add('is-open');
    const closeOverlay = () => overlay?.classList.remove('is-open');

    const openDeleteOverlay = () => deleteOverlay?.classList.add('is-open');
    const closeDeleteOverlay = () => deleteOverlay?.classList.remove('is-open');

    const clearForm = () => {
        if (!form) return;
        form.action = defaultAction;
        usuarioIdInput && (usuarioIdInput.value = '');
        nomeInput && (nomeInput.value = '');
        emailInput && (emailInput.value = '');
        telefoneInput && (telefoneInput.value = '');
        tipoInput && (tipoInput.value = '');
    statusInput && (statusInput.value = '');
    salarioInput && (salarioInput.value = '');
        senhaInput && (senhaInput.value = '');
        senhaConfirmInput && (senhaConfirmInput.value = '');

        // no modo criar, mostrar e exigir senha
        if (senhaGroup) senhaGroup.style.display = '';
        if (senhaConfirmGroup) senhaConfirmGroup.style.display = '';
        if (senhaInput) senhaInput.required = true;
        if (senhaConfirmInput) senhaConfirmInput.required = true;
    };

    openBtn?.addEventListener('click', () => {
        clearForm();
        openOverlay();
    });

    closeBtn?.addEventListener('click', closeOverlay);
    cancelBtn?.addEventListener('click', closeOverlay);
    overlay?.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeOverlay();
        }
    });

    // Fechar modal delete
    closeDeleteBtn?.addEventListener('click', closeDeleteOverlay);
    cancelDeleteBtn?.addEventListener('click', closeDeleteOverlay);
    deleteOverlay?.addEventListener('click', (e) => {
        if (e.target === deleteOverlay) closeDeleteOverlay();
    });

    // Botões de edição
    document.querySelectorAll('.btn-edit').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.currentTarget;
            const data = target.dataset || {};

            if (form) {
                form.action = data.action || defaultAction;
            }

            if (usuarioIdInput) usuarioIdInput.value = data.id || '';
            if (nomeInput) nomeInput.value = data.nome || '';
            if (emailInput) emailInput.value = data.email || '';
            if (telefoneInput) telefoneInput.value = data.telefone || '';
            if (tipoInput) tipoInput.value = data.tipo || '';
            if (statusInput) statusInput.value = data.ativo ?? '';
            if (salarioInput) salarioInput.value = data.salario || '';

            // Para edição, esconder e remover obrigatoriedade da senha
            if (senhaGroup) senhaGroup.style.display = 'none';
            if (senhaConfirmGroup) senhaConfirmGroup.style.display = 'none';
            if (senhaInput) {
                senhaInput.required = false;
                senhaInput.value = '';
            }
            if (senhaConfirmInput) {
                senhaConfirmInput.required = false;
                senhaConfirmInput.value = '';
            }

            openOverlay();
        });
    });

    // Botões de delete -> abre modal customizado e envia POST
    document.querySelectorAll('.btn-delete').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.currentTarget;
            const data = target.dataset || {};

            if (deleteForm && data.action) {
                deleteForm.action = data.action;
            }
            if (deleteConfirmText && data.nome) {
                deleteConfirmText.textContent = `Tem certeza que deseja excluir ${data.nome}?`;
            }

            openDeleteOverlay();
        });
    });

    // Busca em tempo real
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

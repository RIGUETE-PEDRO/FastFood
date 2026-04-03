(() => {
    const roleSource = document.getElementById('kc-role-source');
    const roleValues = roleSource
        ? JSON.parse(roleSource.dataset.roles || '[]')
        : [];
    const addUrlTemplate = roleSource?.dataset.addUrlTemplate || '';
    const removeUrlTemplate = roleSource?.dataset.removeUrlTemplate || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const normalize = (value) => (value || '').trim().toLowerCase();

    function getCanonicalRole(value) {
        const normalized = normalize(value);
        return roleValues.find((role) => normalize(role.nome) === normalized) || null;
    }

    function buildAddUrl(userId) {
        return addUrlTemplate.replace('__USER__', String(userId));
    }

    function buildRemoveUrl(userId, roleId) {
        return removeUrlTemplate
            .replace('__USER__', String(userId))
            .replace('__ROLE__', String(roleId));
    }

    async function requestJson(url, method, body = null) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: body ? JSON.stringify(body) : null,
        });

        if (!response.ok) {
            throw new Error('Falha na requisição');
        }

        return response.json();
    }

    function closeAllAutocomplete() {
        document.querySelectorAll('.kc-autocomplete-list').forEach((list) => {
            list.hidden = true;
            list.innerHTML = '';
        });
    }

    function renderSuggestions(input, list, query) {
        const normalizedQuery = normalize(query);
        const filtered = roleValues
            .filter((role) => normalize(role.nome).includes(normalizedQuery))
            .slice(0, 8);

        list.innerHTML = '';
        if (!filtered.length) {
            list.hidden = true;
            return;
        }

        filtered.forEach((role) => {
            const item = document.createElement('li');
            item.className = 'kc-autocomplete-item';

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'kc-autocomplete-btn';
            button.textContent = role.nome;
            button.addEventListener('click', () => {
                input.value = role.nome;
                list.hidden = true;
            });

            item.appendChild(button);
            list.appendChild(item);
        });

        list.hidden = false;
    }

    function bindRemove(button) {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const item = button.closest('.kc-role-added-item');
            if (!item) return;

            const roleId = item.dataset.roleId;
            const container = button.closest('.kc-roles-expanded');
            const userId = container?.id?.replace('roles_usuario_', '');

            if (!roleId || !userId) {
                item.remove();
                return;
            }

            requestJson(buildRemoveUrl(userId, roleId), 'DELETE')
                .then(() => item.remove())
                .catch(() => alert('Não foi possível remover a role.'));
        });
    }

    document.querySelectorAll('.kc-user-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const panel = document.getElementById(targetId);
            if (!panel) return;

            panel.hidden = !panel.hidden;
            const expanded = !panel.hidden;
            button.setAttribute('aria-expanded', String(expanded));

            const icon = button.querySelector('.kc-user-toggle__icon');
            if (icon) {
                icon.textContent = expanded ? '▴' : '▾';
            }
        });
    });

    document.querySelectorAll('.kc-role-remove-btn').forEach(bindRemove);

    document.querySelectorAll('.kc-role-input').forEach((input) => {
        const autocomplete = input.closest('.kc-autocomplete');
        const list = autocomplete?.querySelector('.kc-autocomplete-list');
        if (!list) return;

        input.addEventListener('focus', () => renderSuggestions(input, list, input.value));
        input.addEventListener('input', () => renderSuggestions(input, list, input.value));
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                list.hidden = true;
                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                const userId = input.id.replace('role_input_', '');
                const addBtn = document.querySelector(`.kc-role-add-btn[data-user-id="${userId}"]`);
                if (addBtn) addBtn.click();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.kc-autocomplete')) {
            closeAllAutocomplete();
        }
    });

    document.querySelectorAll('.kc-role-add-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const userId = button.dataset.userId;
            const input = document.getElementById(`role_input_${userId}`);
            const list = document.querySelector(`#roles_usuario_${userId} .kc-role-added-list`);

            if (!input || !list) return;

            const valorDigitado = input.value.trim();
            if (!valorDigitado) return;

            const role = getCanonicalRole(valorDigitado);
            if (!role) {
                alert('Role inválida. Selecione uma role sugerida.');
                return;
            }

            const vazio = list.querySelector('.kc-role-added-item--empty');
            if (vazio) vazio.remove();

            const repetida = Array.from(list.querySelectorAll('.kc-role-added-item span'))
                .some((span) => normalize(span.textContent) === normalize(role.nome));

            if (repetida) {
                input.value = '';
                return;
            }

            requestJson(buildAddUrl(userId), 'POST', { role_id: role.id })
                .then(() => {
                    const li = document.createElement('li');
                    li.className = 'kc-role-added-item';
                    li.dataset.roleId = String(role.id);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'kc-role-remove-btn';
                    removeBtn.textContent = 'Remover';
                    bindRemove(removeBtn);

                    const span = document.createElement('span');
                    span.textContent = role.nome;

                    li.appendChild(removeBtn);
                    li.appendChild(span);
                    list.appendChild(li);

                    input.value = '';
                    const autocompleteList = input.closest('.kc-autocomplete')?.querySelector('.kc-autocomplete-list');
                    if (autocompleteList) autocompleteList.hidden = true;
                })
                .catch(() => alert('Não foi possível adicionar a role.'));
        });
    });
})();

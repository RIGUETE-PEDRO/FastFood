(() => {
    const source = document.getElementById('kc-grupo-role-source');
    if (!source) return;

    const roles = JSON.parse(source.dataset.roles || '[]');
    const addUrlTemplate = source.dataset.addUrlTemplate || '';
    const removeUrlTemplate = source.dataset.removeUrlTemplate || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const normalize = (value) => (value || '').trim().toLowerCase();

    const getCanonicalRole = (value) => {
        const normalized = normalize(value);
        return roles.find((role) => normalize(role.nome) === normalized) || null;
    };

    const buildAddUrl = (grupoId) => addUrlTemplate.replace('__GRUPO__', String(grupoId));
    const buildRemoveUrl = (grupoId, roleId) => removeUrlTemplate
        .replace('__GRUPO__', String(grupoId))
        .replace('__ROLE__', String(roleId));

    function positionSuggestions(input, list) {
        const inputRect = input.getBoundingClientRect();
        const viewportPadding = 12;
        const dropdownGap = 6;
        const spaceAbove = Math.max(0, inputRect.top - viewportPadding - dropdownGap);
        const spaceBelow = Math.max(0, window.innerHeight - inputRect.bottom - viewportPadding - dropdownGap);
        const desiredHeight = Math.min(360, list.scrollHeight);
        const openAbove = spaceBelow < desiredHeight && spaceAbove > spaceBelow;
        const availableSpace = openAbove ? spaceAbove : spaceBelow;

        list.classList.toggle('kc-autocomplete-list--above', openAbove);
        list.style.maxHeight = `${Math.max(0, Math.min(360, availableSpace))}px`;
    }

    function closeSuggestions(list) {
        list.hidden = true;
        list.innerHTML = '';
        list.classList.remove('kc-autocomplete-list--above');
        list.style.maxHeight = '';
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

    function renderSuggestions(input, list, query) {
        const filtered = roles
            .filter((role) => normalize(role.nome).includes(normalize(query)));

        list.innerHTML = '';
        if (!filtered.length) {
            closeSuggestions(list);
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
                closeSuggestions(list);
            });

            item.appendChild(button);
            list.appendChild(item);
        });

        list.hidden = false;
        positionSuggestions(input, list);
    }

    function repositionOpenSuggestions() {
        document.querySelectorAll('.kc-autocomplete-list:not([hidden])').forEach((list) => {
            const input = list.closest('.kc-autocomplete')?.querySelector('.kc-role-input');
            if (input) positionSuggestions(input, list);
        });
    }

    function bindRemove(button) {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const item = button.closest('.kc-role-added-item');
            if (!item) return;

            const roleId = item.dataset.roleId;
            const grupoId = item.dataset.grupoId;
            if (!roleId || !grupoId) return;

            requestJson(buildRemoveUrl(grupoId, roleId), 'DELETE')
                .then(() => item.remove())
                .catch(() => alert('Não foi possível remover a role do grupo.'));
        });
    }

    document.querySelectorAll('.kc-user-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const panel = document.getElementById(button.dataset.target);
            if (!panel) return;

            panel.hidden = !panel.hidden;
            const expanded = !panel.hidden;
            button.setAttribute('aria-expanded', String(expanded));

            const icon = button.querySelector('.kc-user-toggle__icon');
            if (icon) icon.textContent = expanded ? '▴' : '▾';
        });
    });

    document.querySelectorAll('.kc-role-remove-btn').forEach(bindRemove);

    document.querySelectorAll('.kc-role-input').forEach((input) => {
        const list = input.closest('.kc-autocomplete')?.querySelector('.kc-autocomplete-list');
        if (!list) return;

        input.addEventListener('focus', () => renderSuggestions(input, list, input.value));
        input.addEventListener('input', () => renderSuggestions(input, list, input.value));
    });

    window.addEventListener('resize', repositionOpenSuggestions);
    document.addEventListener('scroll', repositionOpenSuggestions, true);

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.kc-autocomplete')) {
            document.querySelectorAll('.kc-autocomplete-list').forEach((list) => {
                closeSuggestions(list);
            });
        }
    });

    document.querySelectorAll('.kc-role-add-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const grupoId = button.dataset.grupoId;
            const input = document.getElementById(`role_input_grupo_${grupoId}`);
            const list = document.querySelector(`#roles_grupo_${grupoId} .kc-role-added-list`);

            if (!grupoId || !input || !list) return;

            const role = getCanonicalRole(input.value.trim());
            if (!role) {
                alert('Roles inválida. Selecione uma role da lista.');
                return;
            }

            const repetida = Array.from(list.querySelectorAll('.kc-role-added-item span'))
                .some((span) => normalize(span.textContent) === normalize(role.nome));

            if (repetida) {
                input.value = '';
                return;
            }

            requestJson(buildAddUrl(grupoId), 'POST', { role_id: role.id })
                .then(() => {
                    const vazio = list.querySelector('.kc-role-added-item--empty');
                    if (vazio) vazio.remove();

                    const li = document.createElement('li');
                    li.className = 'kc-role-added-item';
                    li.dataset.roleId = String(role.id);
                    li.dataset.grupoId = String(grupoId);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'kc-role-remove-btn';
                    removeBtn.textContent = 'Remover';
                    bindRemove(removeBtn);

                    const span = document.createElement('span');
                    span.textContent = role.nome;

                    li.appendChild(span);
                    li.appendChild(removeBtn);
                    list.appendChild(li);

                    input.value = '';
                })
                .catch(() => alert('Não foi possível adicionar a role ao grupo.'));
        });
    });
})();

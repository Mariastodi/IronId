'use strict';

const getElement = (elementId) => document.getElementById(elementId);
const membershipLabels = {
    active: 'Em dia',
    expiring_soon: 'Vencendo',
    expired: 'Vencido',
    inactive: 'Inativo',
};

let accessToken = sessionStorage.getItem('ironid_token');
let currentPage = 1;
let lastPage = 1;
let searchVersion = 0;
let searchTimer;

function showNotice(message) {
    getElement('notice').textContent = message;
    getElement('notice').hidden = !message;
}

function updateSessionVisibility() {
    getElement('login').hidden = Boolean(accessToken);
    getElement('workspace').hidden = !accessToken;
    getElement('logout').hidden = !accessToken;
}

async function requestApi(endpoint, options = {}) {
    const response = await fetch(`/api${endpoint}`, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            Authorization: `Bearer ${accessToken}`,
            ...options.headers,
        },
    });
    const payload = response.status === 204 ? {} : await response.json();

    if (response.status === 401) {
        sessionStorage.removeItem('ironid_token');
        accessToken = null;
        updateSessionVisibility();
    }

    if (!response.ok) {
        const message = payload.errors
            ? Object.values(payload.errors).flat().join(' ')
            : payload.message || 'Não foi possível concluir a operação.';
        throw new Error(message);
    }

    return payload;
}

function appendTableCell(row, text, tagName = 'span', className = '') {
    const tableCell = document.createElement('td');
    const content = document.createElement(tagName);
    content.textContent = text;
    content.className = className;
    tableCell.append(content);
    row.append(tableCell);
    return tableCell;
}

function createActionButton(label, action, member) {
    const button = document.createElement('button');
    button.textContent = label;
    button.className = 'secondary';
    button.addEventListener('click', async () => {
        button.disabled = true;
        try {
            const result = await action();
            if (result) {
                showNotice(`${label} realizado para ${member.name}.`);
                await refreshDashboard();
            }
        } catch (error) {
            showNotice(error.message);
        } finally {
            button.disabled = false;
        }
    });
    return button;
}

function createMemberRow(member) {
    const row = document.createElement('tr');
    const nameCell = appendTableCell(row, member.name, 'strong');
    const registration = document.createElement('small');
    registration.textContent = member.matricula;
    nameCell.append(registration);
    appendTableCell(row, member.plan?.name || 'Sem plano');
    appendTableCell(row, membershipLabels[member.membership_status], 'span', `badge ${member.membership_status}`);
    appendTableCell(row, member.face_enrolled ? 'Cadastrada' : 'Pendente');

    const actionsCell = document.createElement('td');
    const actions = document.createElement('div');
    actions.className = 'actions';
    actions.append(createActionButton('Check-in', () => requestApi('/check-ins', {
        method: 'POST',
        body: JSON.stringify({ member_id: member.id }),
    }), member));
    actions.append(createActionButton('Renovar', () => {
        if (!confirm(`Renovar o plano de ${member.name}?`)) return null;
        return requestApi(`/members/${member.id}/renew-plan`, { method: 'POST' });
    }, member));

    const enrollmentLink = document.createElement('a');
    enrollmentLink.className = 'button secondary enrollment-link';
    enrollmentLink.textContent = 'Rosto';
    enrollmentLink.href = `/enroll?matricula=${encodeURIComponent(member.matricula)}`;
    actions.append(enrollmentLink);
    actionsCell.append(actions);
    row.append(actionsCell);
    return row;
}

async function loadMembers() {
    const requestVersion = ++searchVersion;
    const searchTerm = encodeURIComponent(getElement('search').value);
    const payload = await requestApi(`/members?per_page=8&page=${currentPage}&name=${searchTerm}`);
    if (requestVersion !== searchVersion) return;

    const tableBody = getElement('members');
    tableBody.replaceChildren(...payload.data.map(createMemberRow));
    lastPage = payload.meta.last_page;

    if (!payload.data.length) {
        const emptyRow = document.createElement('tr');
        appendTableCell(emptyRow, 'Nenhum aluno encontrado.').colSpan = 5;
        tableBody.append(emptyRow);
    }

    getElement('page-label').textContent = `Página ${currentPage} de ${lastPage}`;
    getElement('previous').disabled = currentPage <= 1;
    getElement('next').disabled = currentPage >= lastPage;
}

async function refreshDashboard() {
    await loadMembers();
    const summary = await requestApi('/dashboard');
    getElement('today').textContent = summary.data.today_check_ins_count;
    getElement('total').textContent = summary.data.members_count;
    getElement('enrolled').textContent = summary.data.enrolled_members_count;
}

async function initializeDashboard() {
    updateSessionVisibility();
    if (!accessToken) return;
    try {
        const operator = await requestApi('/me');
        getElement('operator').textContent = operator.name;
        await refreshDashboard();
    } catch (error) {
        showNotice(error.message);
    }
}

getElement('login-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const submitButton = event.target.querySelector('button');
    submitButton.disabled = true;
    try {
        const credentials = {
            email: getElement('email').value,
            password: getElement('password').value,
        };
        const session = await requestApi('/login', {
            method: 'POST',
            body: JSON.stringify(credentials),
        });
        accessToken = session.token;
        sessionStorage.setItem('ironid_token', accessToken);
        getElement('password').value = '';
        showNotice('');
        await initializeDashboard();
    } catch (error) {
        showNotice(error.message);
    } finally {
        submitButton.disabled = false;
    }
});

getElement('logout').addEventListener('click', async () => {
    try {
        await requestApi('/logout', { method: 'POST' });
        sessionStorage.removeItem('ironid_token');
        accessToken = null;
        updateSessionVisibility();
        showNotice('Sessão encerrada.');
    } catch (error) {
        showNotice(error.message);
    }
});

getElement('search').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage = 1;
        loadMembers().catch(error => showNotice(error.message));
    }, 250);
});

for (const [elementId, pageOffset] of [['previous', -1], ['next', 1]]) {
    getElement(elementId).addEventListener('click', () => {
        currentPage += pageOffset;
        loadMembers().catch(error => showNotice(error.message));
    });
}

getElement('new-member').addEventListener('click', async () => {
    try {
        const plans = await requestApi('/plans');
        const planSelect = getElement('plan');
        planSelect.replaceChildren();
        for (const plan of plans.data) {
            const option = document.createElement('option');
            option.value = plan.id;
            option.textContent = plan.name;
            planSelect.append(option);
        }
        getElement('form-error').textContent = '';
        getElement('member-dialog').showModal();
    } catch (error) {
        showNotice(error.message);
    }
});

getElement('cancel').addEventListener('click', () => getElement('member-dialog').close());

getElement('member-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const submitButton = event.target.querySelector('button');
    submitButton.disabled = true;
    try {
        const memberData = Object.fromEntries(new FormData(event.target));
        await requestApi('/members', { method: 'POST', body: JSON.stringify(memberData) });
        event.target.reset();
        getElement('member-dialog').close();
        showNotice('Aluno cadastrado. Use Rosto para cadastrar a biometria.');
        currentPage = 1;
        await refreshDashboard();
    } catch (error) {
        getElement('form-error').textContent = error.message;
    } finally {
        submitButton.disabled = false;
    }
});

initializeDashboard();

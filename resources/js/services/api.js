const DIAGNOSIS_ENDPOINT = '/api/diagnosis/analyze';
const AUTH_REGISTER_ENDPOINT = '/api/auth/register';
const AUTH_LOGIN_ENDPOINT = '/api/auth/login';
const AUTH_LOGOUT_ENDPOINT = '/api/auth/logout';
const AUTH_ME_ENDPOINT = '/api/auth/me';
const PORTAL_ENDPOINT = '/api/portal';
const PROFILE_ENDPOINT = '/api/profile';
const HISTORY_ENDPOINT = '/api/history';

let authToken = localStorage.getItem('auth_token') ?? '';

export function setAuthToken(token) {
    authToken = String(token ?? '');
    if (authToken) {
        localStorage.setItem('auth_token', authToken);
    } else {
        localStorage.removeItem('auth_token');
    }
}

function requestHeaders(extra = {}) {
    const headers = { Accept: 'application/json', ...extra };
    if (authToken) {
        headers.Authorization = `Bearer ${authToken}`;
    }
    return headers;
}

async function parseResponse(response) {
    if (!response.ok) {
        const body = await response.json().catch(() => ({}));
        throw new Error(body.message || JSON.stringify(body.errors || body) || 'Request failed');
    }

    return response.json();
}

export function analyzeDiagnosis(formData) {
    return fetch(DIAGNOSIS_ENDPOINT, {
        method: 'POST',
        headers: requestHeaders(),
        body: formData,
    }).then(parseResponse);
}

export function register(payload) {
    return fetch(AUTH_REGISTER_ENDPOINT, {
        method: 'POST',
        headers: requestHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify(payload),
    }).then(parseResponse);
}

export function login(payload) {
    return fetch(AUTH_LOGIN_ENDPOINT, {
        method: 'POST',
        headers: requestHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify(payload),
    }).then(parseResponse);
}

export function logout() {
    return fetch(AUTH_LOGOUT_ENDPOINT, {
        method: 'POST',
        headers: requestHeaders(),
    }).then(parseResponse);
}

export function me() {
    return fetch(AUTH_ME_ENDPOINT, {
        method: 'GET',
        headers: requestHeaders(),
    }).then(parseResponse);
}

export function getProfile() {
    return fetch(PROFILE_ENDPOINT, {
        method: 'GET',
        headers: requestHeaders(),
    }).then(parseResponse);
}

export function getPortal() {
    return fetch(PORTAL_ENDPOINT, {
        method: 'GET',
        headers: requestHeaders(),
    }).then(parseResponse);
}

export function updateEmail(payload) {
    return fetch(`${PROFILE_ENDPOINT}/email`, {
        method: 'PATCH',
        headers: requestHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify(payload),
    }).then(parseResponse);
}

export function updatePassword(payload) {
    return fetch(`${PROFILE_ENDPOINT}/password`, {
        method: 'PATCH',
        headers: requestHeaders({ 'Content-Type': 'application/json' }),
        body: JSON.stringify(payload),
    }).then(parseResponse);
}

export function getHistory(page = 1) {
    return fetch(`${HISTORY_ENDPOINT}?page=${page}`, {
        method: 'GET',
        headers: requestHeaders(),
    }).then(parseResponse);
}

export function getHistoryItem(id) {
    return fetch(`${HISTORY_ENDPOINT}/${id}`, {
        method: 'GET',
        headers: requestHeaders(),
    }).then(parseResponse);
}

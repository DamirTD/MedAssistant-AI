import { reactive } from 'vue';
import { login, logout, me, register, setAuthToken } from '../services/api';

const state = reactive({
    user: null,
    ready: false,
});

function setUser(user) {
    state.user = user ?? null;
}

async function initAuth() {
    try {
        const response = await me();
        setUser(response.user);
    } catch {
        setAuthToken('');
        setUser(null);
    } finally {
        state.ready = true;
    }
}

async function loginUser(payload) {
    const response = await login(payload);
    setAuthToken(response.token);
    setUser(response.user);
    return response.user;
}

async function registerUser(payload) {
    const response = await register(payload);
    setAuthToken(response.token);
    setUser(response.user);
    return response.user;
}

async function logoutUser() {
    try {
        await logout();
    } finally {
        setAuthToken('');
        setUser(null);
    }
}

export function useAuthStore() {
    return {
        state,
        setUser,
        initAuth,
        loginUser,
        registerUser,
        logoutUser,
        isAuthenticated: () => Boolean(state.user),
    };
}

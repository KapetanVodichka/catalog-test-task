import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { apiRequest } from './useApi';

const storageEnabled = typeof window !== 'undefined';
const tokenCookieName = 'admin_token';
const tokenCookieMaxAgeSeconds = 60 * 60 * 24 * 7;

const tokenState = ref(storageEnabled ? (window.localStorage.getItem('auth_token') ?? '') : '');
const userState = ref(null);

function writeTokenCookie(token) {
    if (!storageEnabled) {
        return;
    }

    document.cookie = `${tokenCookieName}=${encodeURIComponent(token)}; path=/; max-age=${tokenCookieMaxAgeSeconds}; samesite=lax`;
}

function clearTokenCookie() {
    if (!storageEnabled) {
        return;
    }

    document.cookie = `${tokenCookieName}=; path=/; max-age=0; samesite=lax`;
}

if (storageEnabled) {
    const rawUser = window.localStorage.getItem('auth_user');

    if (rawUser) {
        try {
            userState.value = JSON.parse(rawUser);
        } catch {
            userState.value = null;
        }
    }

    if (tokenState.value) {
        writeTokenCookie(tokenState.value);
    }
}

function saveSession(token, user) {
    tokenState.value = token;
    userState.value = user;

    if (!storageEnabled) {
        return;
    }

    window.localStorage.setItem('auth_token', token);
    window.localStorage.setItem('auth_user', JSON.stringify(user));
    writeTokenCookie(token);
}

function clearSession() {
    tokenState.value = '';
    userState.value = null;

    if (!storageEnabled) {
        return;
    }

    window.localStorage.removeItem('auth_token');
    window.localStorage.removeItem('auth_user');
    clearTokenCookie();
}

export function useAuth() {
    const isAuthenticated = computed(() => Boolean(tokenState.value));

    async function login(credentials) {
        const payload = await apiRequest('/api/login', {
            method: 'POST',
            body: credentials,
        });

        saveSession(payload.token, payload.user);

        return payload;
    }

    async function logout({ redirect = true } = {}) {
        if (tokenState.value) {
            try {
                await apiRequest('/api/logout', {
                    method: 'POST',
                    token: tokenState.value,
                });
            } catch {
            }
        }

        clearSession();

        if (redirect) {
            router.visit('/login');
        }
    }

    function requireAuth() {
        if (isAuthenticated.value) {
            return true;
        }

        router.visit('/login');
        return false;
    }

    return {
        token: tokenState,
        user: userState,
        isAuthenticated,
        login,
        logout,
        clearSession,
        requireAuth,
    };
}

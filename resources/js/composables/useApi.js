export function buildQueryString(params = {}) {
    const query = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') {
            return;
        }

        query.append(key, String(value));
    });

    const queryString = query.toString();

    return queryString ? `?${queryString}` : '';
}

export async function apiRequest(path, options = {}) {
    const {
        method = 'GET',
        params = {},
        body = null,
        token = '',
        signal = undefined,
        headers = {},
    } = options;

    const requestHeaders = {
        Accept: 'application/json',
        ...headers,
    };

    if (token) {
        requestHeaders.Authorization = `Bearer ${token}`;
    }

    const fetchOptions = {
        method,
        headers: requestHeaders,
        signal,
    };

    if (body !== null) {
        requestHeaders['Content-Type'] = 'application/json';
        fetchOptions.body = JSON.stringify(body);
    }

    const response = await fetch(`${path}${buildQueryString(params)}`, fetchOptions);

    if (response.status === 204) {
        return null;
    }

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(payload.message || 'Request failed');
        error.status = response.status;
        error.errors = payload.errors || {};
        error.payload = payload;
        throw error;
    }

    return payload;
}

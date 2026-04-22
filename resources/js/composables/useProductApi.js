import { apiRequest } from './useApi';

export function useProductApi() {
    async function getProducts({ page = 1, perPage = 12, categoryId = '', search = '', signal } = {}) {
        return apiRequest('/api/products', {
            params: {
                page,
                per_page: perPage,
                category_id: categoryId,
                search,
            },
            signal,
        });
    }

    async function getProduct(id) {
        return apiRequest(`/api/products/${id}`);
    }

    async function createProduct(data, token) {
        return apiRequest('/api/products', {
            method: 'POST',
            body: data,
            token,
        });
    }

    async function updateProduct(id, data, token) {
        return apiRequest(`/api/products/${id}`, {
            method: 'PUT',
            body: data,
            token,
        });
    }

    async function deleteProduct(id, token) {
        return apiRequest(`/api/products/${id}`, {
            method: 'DELETE',
            token,
        });
    }

    return {
        getProducts,
        getProduct,
        createProduct,
        updateProduct,
        deleteProduct,
    };
}

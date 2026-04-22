import { apiRequest } from './useApi';

export function useCategoryApi() {
    async function getCategories() {
        const payload = await apiRequest('/api/categories');
        return payload.data ?? [];
    }

    return {
        getCategories,
    };
}

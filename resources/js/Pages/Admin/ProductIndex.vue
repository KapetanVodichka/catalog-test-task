<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '../../Layouts/MainLayout.vue';
import PaginationControls from '../../Components/PaginationControls.vue';
import { useAuth } from '../../composables/useAuth';
import { useCategoryApi } from '../../composables/useCategoryApi';
import { useProductApi } from '../../composables/useProductApi';

const { token, requireAuth, logout } = useAuth();
const { getCategories } = useCategoryApi();
const { getProducts, deleteProduct } = useProductApi();

const categories = ref([]);
const selectedCategory = ref('');
const searchTerm = ref('');
const products = ref([]);
const initialLoading = ref(true);
const listLoading = ref(false);
const categoriesLoading = ref(false);
const deletingId = ref(null);
const confirmDeleteProduct = ref(null);
const error = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = 12;
let searchDebounceTimer = null;
let productsRequestController = null;

async function loadCategories() {
    categoriesLoading.value = true;

    try {
        categories.value = await getCategories();
    } catch {
        error.value = 'Не удалось загрузить категории.';
    } finally {
        categoriesLoading.value = false;
    }
}

async function loadProducts(page = 1) {
    listLoading.value = true;
    error.value = '';

    if (productsRequestController) {
        productsRequestController.abort();
    }

    const controller = new AbortController();
    productsRequestController = controller;

    try {
        const payload = await getProducts({
            page,
            perPage,
            categoryId: selectedCategory.value,
            search: searchTerm.value,
            signal: controller.signal,
        });

        products.value = payload.data ?? [];
        currentPage.value = payload.meta?.current_page ?? 1;
        lastPage.value = payload.meta?.last_page ?? 1;
    } catch (e) {
        if (e.name === 'AbortError') {
            return;
        }

        if (e.status === 401) {
            await logout();
            return;
        }

        error.value = 'Не удалось загрузить товары.';
    } finally {
        if (productsRequestController === controller) {
            productsRequestController = null;
            listLoading.value = false;
            initialLoading.value = false;
        }
    }
}

function openDeleteModal(product) {
    if (deletingId.value) {
        return;
    }

    confirmDeleteProduct.value = product;
}

function closeDeleteModal() {
    if (deletingId.value) {
        return;
    }

    confirmDeleteProduct.value = null;
}

async function removeProduct() {
    if (deletingId.value) {
        return;
    }

    if (!confirmDeleteProduct.value) {
        return;
    }

    deletingId.value = confirmDeleteProduct.value.id;
    error.value = '';

    try {
        await deleteProduct(confirmDeleteProduct.value.id, token.value);
        await loadProducts(currentPage.value);
        confirmDeleteProduct.value = null;
    } catch (e) {
        if (e.status === 401) {
            await logout();
            return;
        }

        error.value = 'Не удалось удалить товар.';
    } finally {
        deletingId.value = null;
    }
}

function goToPage(page) {
    loadProducts(page);
}

function onFilterChange() {
    loadProducts(1);
}

function onSearchInput() {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        loadProducts(1);
    }, 450);
}

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    if (productsRequestController) {
        productsRequestController.abort();
    }
});

onMounted(async () => {
    if (!requireAuth()) {
        return;
    }

    await loadCategories();
    await loadProducts(1);
});
</script>

<template>
    <MainLayout title="Управление товарами">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Управление товарами</h1>
                    <p class="mt-1 text-sm text-slate-600">Создание, редактирование и удаление товаров.</p>
                </div>

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-end">
                    <div class="w-full sm:w-64">
                        <label for="admin-search-products" class="mb-1 block text-sm font-medium text-slate-700">Поиск</label>
                        <input
                            id="admin-search-products"
                            v-model="searchTerm"
                            type="text"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                            placeholder="Название или описание"
                            @input="onSearchInput"
                        >
                    </div>

                    <div class="w-full sm:w-64">
                        <label for="admin-category-filter" class="mb-1 block text-sm font-medium text-slate-700">Категория</label>
                        <select
                            id="admin-category-filter"
                            v-model="selectedCategory"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                            :disabled="categoriesLoading"
                            @change="onFilterChange"
                        >
                            <option value="">Все категории</option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <Link
                        href="/admin/products/create"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700"
                    >
                        Добавить товар
                    </Link>
                </div>
            </div>

            <div v-if="error" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ error }}
            </div>

            <div class="h-5 text-sm text-slate-500">
                {{ listLoading && !initialLoading ? 'Обновление списка...' : '' }}
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Название</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Категория</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Цена</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="initialLoading">
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-600">Загрузка...</td>
                        </tr>

                        <tr v-else-if="products.length === 0">
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-600">Товары не найдены.</td>
                        </tr>

                        <tr v-for="product in products" :key="product.id" class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ product.name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ product.category?.name || 'Без категории' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ Number(product.price).toFixed(2) }} ₽</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/admin/products/${product.id}/edit`"
                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                    >
                                        Редактировать
                                    </Link>

                                    <button
                                        type="button"
                                        class="rounded-lg border border-rose-300 px-3 py-1.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                                        :disabled="Boolean(deletingId)"
                                        @click="openDeleteModal(product)"
                                    >
                                        {{ deletingId === product.id ? 'Удаление...' : 'Удалить' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <PaginationControls
                :current-page="currentPage"
                :last-page="lastPage"
                :loading="listLoading"
                @go="goToPage"
            />
        </section>

        <div
            v-if="confirmDeleteProduct"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/55 px-4 backdrop-blur-[2px]"
            @click.self="closeDeleteModal"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-900">Подтвердите удаление</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Вы действительно хотите удалить товар
                    <span class="font-medium text-slate-900">«{{ confirmDeleteProduct.name }}»</span>?
                </p>
                <p class="mt-1 text-xs text-slate-500">Действие нельзя отменить.</p>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="Boolean(deletingId)"
                        @click="closeDeleteModal"
                    >
                        Отмена
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="Boolean(deletingId)"
                        @click="removeProduct"
                    >
                        {{ deletingId ? 'Удаление...' : 'Удалить' }}
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

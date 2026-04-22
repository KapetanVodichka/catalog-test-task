<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '../../Layouts/MainLayout.vue';
import PaginationControls from '../../Components/PaginationControls.vue';
import { useCategoryApi } from '../../composables/useCategoryApi';
import { useProductApi } from '../../composables/useProductApi';

const { getCategories } = useCategoryApi();
const { getProducts } = useProductApi();

const categories = ref([]);
const selectedCategory = ref('');
const searchTerm = ref('');
const products = ref([]);
const initialLoading = ref(true);
const listLoading = ref(false);
const categoriesLoading = ref(false);
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

        error.value = 'Не удалось загрузить товары. Попробуйте еще раз.';
    } finally {
        if (productsRequestController === controller) {
            productsRequestController = null;
            listLoading.value = false;
            initialLoading.value = false;
        }
    }
}

function goToPage(page) {
    loadProducts(page);
}

function onSearchInput() {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        loadProducts(1);
    }, 450);
}

watch(selectedCategory, () => {
    loadProducts(1);
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    if (productsRequestController) {
        productsRequestController.abort();
    }
});

onMounted(async () => {
    await loadCategories();
    await loadProducts(1);
});
</script>

<template>
    <MainLayout title="Каталог товаров">
        <section class="space-y-6">
            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Каталог товаров</h1>
                    <p class="mt-1 text-sm text-slate-600">Используйте поиск и фильтр по категории.</p>
                </div>

                <div class="grid w-full gap-3 sm:max-w-2xl sm:grid-cols-2">
                    <div>
                        <label for="search-products" class="mb-1 block text-sm font-medium text-slate-700">Поиск</label>
                        <input
                            id="search-products"
                            v-model="searchTerm"
                            type="text"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                            placeholder="Название или описание"
                            @input="onSearchInput"
                        >
                    </div>

                    <div>
                        <label for="category-filter" class="mb-1 block text-sm font-medium text-slate-700">Категория</label>
                        <select
                            id="category-filter"
                            v-model="selectedCategory"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                            :disabled="categoriesLoading"
                        >
                            <option value="">Все категории</option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div v-if="error" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ error }}
            </div>

            <div v-if="initialLoading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="item in 6"
                    :key="item"
                    class="h-44 animate-pulse rounded-2xl border border-slate-200 bg-slate-100"
                />
            </div>

            <div v-else-if="products.length === 0" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">
                Товары не найдены.
            </div>

            <div v-else>
                <div class="mb-3 h-5 text-sm text-slate-500">
                    {{ listLoading ? 'Обновление списка...' : '' }}
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="product in products"
                        :key="product.id"
                        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {{ product.category?.name || 'Без категории' }}
                            </span>
                            <span class="text-sm font-semibold text-slate-900">{{ Number(product.price).toFixed(2) }} ₽</span>
                        </div>

                        <h2 class="line-clamp-2 text-lg font-semibold text-slate-900">{{ product.name }}</h2>
                        <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ product.description || 'Описание отсутствует.' }}</p>

                        <Link
                            :href="`/product/${product.id}`"
                            class="mt-4 inline-flex items-center text-sm font-medium text-slate-900 transition group-hover:text-teal-700"
                        >
                            Открыть карточку
                        </Link>
                    </article>
                </div>
            </div>

            <PaginationControls
                :current-page="currentPage"
                :last-page="lastPage"
                :loading="listLoading"
                @go="goToPage"
            />
        </section>
    </MainLayout>
</template>

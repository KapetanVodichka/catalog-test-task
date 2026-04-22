<script setup>
import { onMounted, reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import MainLayout from '../../Layouts/MainLayout.vue';
import ProductFormFields from '../../Components/ProductFormFields.vue';
import { useAuth } from '../../composables/useAuth';
import { useCategoryApi } from '../../composables/useCategoryApi';
import { useProductApi } from '../../composables/useProductApi';

const props = defineProps({
    mode: {
        type: String,
        required: true,
    },
    productId: {
        type: Number,
        default: null,
    },
});

const { token, requireAuth, logout } = useAuth();
const { getCategories } = useCategoryApi();
const { getProduct, createProduct, updateProduct } = useProductApi();

const form = reactive({
    name: '',
    category_id: '',
    description: '',
    price: '',
});

const categories = ref([]);
const loading = ref(false);
const initialLoading = ref(true);
const formErrors = ref({});
const submitError = ref('');

const isEditMode = props.mode === 'edit';

async function loadCategories() {
    categories.value = await getCategories();
}

async function loadProduct() {
    if (!isEditMode || !props.productId) {
        return;
    }

    const payload = await getProduct(props.productId);
    const product = payload.data;

    form.name = product.name ?? '';
    form.category_id = product.category_id ? String(product.category_id) : '';
    form.description = product.description ?? '';
    form.price = product.price ?? '';
}

function validateForm() {
    const errors = {};

    if (!String(form.name).trim()) {
        errors.name = 'Поле обязательно.';
    }

    if (!String(form.category_id).trim()) {
        errors.category_id = 'Выберите категорию.';
    }

    const priceValue = Number(form.price);

    if (!form.price || Number.isNaN(priceValue) || priceValue <= 0) {
        errors.price = 'Цена должна быть больше 0.';
    }

    formErrors.value = errors;

    return Object.keys(errors).length === 0;
}

async function submit() {
    if (loading.value) {
        return;
    }

    submitError.value = '';

    if (!validateForm()) {
        return;
    }

    loading.value = true;

    const payload = {
        name: String(form.name).trim(),
        category_id: Number(form.category_id),
        description: String(form.description || '').trim(),
        price: Number(form.price),
    };

    try {
        if (isEditMode && props.productId) {
            await updateProduct(props.productId, payload, token.value);
        } else {
            await createProduct(payload, token.value);
        }

        router.visit('/admin/products');
    } catch (e) {
        if (e.status === 401) {
            await logout();
            return;
        }

        const backendErrors = Object.fromEntries(
            Object.entries(e.errors || {}).map(([key, value]) => [key, value[0]]),
        );

        formErrors.value = {
            ...formErrors.value,
            ...backendErrors,
        };

        if (Object.keys(backendErrors).length === 0) {
            submitError.value = isEditMode
                ? 'Не удалось обновить товар.'
                : 'Не удалось создать товар.';
        }
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    if (!requireAuth()) {
        return;
    }

    initialLoading.value = true;

    try {
        await loadCategories();
        await loadProduct();
    } catch (e) {
        if (e.status === 401) {
            await logout();
            return;
        }

        if (e.status === 404) {
            submitError.value = 'Товар не найден.';
        } else {
            submitError.value = 'Не удалось загрузить данные формы.';
        }
    } finally {
        initialLoading.value = false;
    }
});
</script>

<template>
    <MainLayout :title="isEditMode ? 'Редактирование товара' : 'Создание товара'">
        <section class="mx-auto w-full max-w-3xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                    {{ isEditMode ? 'Редактирование товара' : 'Добавление товара' }}
                </h1>

                <Link
                    href="/admin/products"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                >
                    К списку
                </Link>
            </div>

            <div v-if="submitError" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ submitError }}
            </div>

            <div v-if="initialLoading" class="py-6 text-sm text-slate-600">Загрузка...</div>

            <ProductFormFields
                v-else
                :form="form"
                :categories="categories"
                :errors="formErrors"
                :loading="loading"
                :submit-label="isEditMode ? 'Сохранить изменения' : 'Создать товар'"
                @submit="submit"
            />
        </section>
    </MainLayout>
</template>

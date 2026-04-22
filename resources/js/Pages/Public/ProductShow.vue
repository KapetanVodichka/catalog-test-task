<script setup>
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '../../Layouts/MainLayout.vue';
import { useProductApi } from '../../composables/useProductApi';

const props = defineProps({
    productId: {
        type: Number,
        required: true,
    },
});

const { getProduct } = useProductApi();

const product = ref(null);
const loading = ref(true);
const error = ref('');

async function loadProduct() {
    loading.value = true;
    error.value = '';

    try {
        const payload = await getProduct(props.productId);
        product.value = payload.data ?? null;
    } catch (e) {
        if (e.status === 404) {
            error.value = 'Товар не найден.';
        } else {
            error.value = 'Не удалось загрузить товар. Попробуйте еще раз.';
        }
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadProduct();
});
</script>

<template>
    <MainLayout title="Карточка товара">
        <section class="space-y-4">
            <Link href="/" class="inline-flex items-center text-sm font-medium text-slate-700 transition hover:text-slate-900">
                ← Назад к каталогу
            </Link>

            <div v-if="loading" class="h-72 animate-pulse rounded-2xl border border-slate-200 bg-slate-100" />

            <div v-else-if="error" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ error }}
            </div>

            <article v-else-if="product" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                        {{ product.category?.name || 'Без категории' }}
                    </span>
                    <span class="text-sm text-slate-500">ID: {{ product.id }}</span>
                </div>

                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ product.name }}</h1>

                <p class="mt-4 text-lg font-semibold text-slate-900">{{ Number(product.price).toFixed(2) }} ₽</p>

                <p class="mt-5 whitespace-pre-line text-slate-700">
                    {{ product.description || 'Описание отсутствует.' }}
                </p>
            </article>
        </section>
    </MainLayout>
</template>

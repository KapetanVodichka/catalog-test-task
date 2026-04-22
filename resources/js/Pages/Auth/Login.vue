<script setup>
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '../../Layouts/MainLayout.vue';
import { useAuth } from '../../composables/useAuth';

const { login, isAuthenticated } = useAuth();

const form = reactive({
    email: 'admin@example.com',
    password: 'password',
});

const errors = ref({});
const submitError = ref('');
const loading = ref(false);

async function handleSubmit() {
    if (loading.value) {
        return;
    }

    errors.value = {};
    submitError.value = '';

    if (!form.email) {
        errors.value.email = 'Введите email.';
    }

    if (!form.password) {
        errors.value.password = 'Введите пароль.';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    loading.value = true;

    try {
        await login(form);
        router.visit('/admin/products');
    } catch (e) {
        errors.value = {
            ...errors.value,
            ...Object.fromEntries(Object.entries(e.errors || {}).map(([key, value]) => [key, value[0]])),
        };

        if (Object.keys(errors.value).length === 0) {
            submitError.value = 'Не удалось выполнить вход.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <MainLayout title="Вход администратора">
        <section class="mx-auto w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Вход</h1>
            <p class="mt-1 text-sm text-slate-600">Авторизация для управления товарами.</p>

            <div
                v-if="isAuthenticated"
                class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"
            >
                Вы уже авторизованы.
            </div>

            <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-medium text-slate-800">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                        placeholder="admin@example.com"
                    >
                    <p v-if="errors.email" class="text-sm text-rose-600">{{ errors.email }}</p>
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-sm font-medium text-slate-800">Пароль</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                        placeholder="Введите пароль"
                    >
                    <p v-if="errors.password" class="text-sm text-rose-600">{{ errors.password }}</p>
                </div>

                <div v-if="submitError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    {{ submitError }}
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="loading"
                >
                    {{ loading ? 'Входим...' : 'Войти' }}
                </button>
            </form>
        </section>
    </MainLayout>
</template>

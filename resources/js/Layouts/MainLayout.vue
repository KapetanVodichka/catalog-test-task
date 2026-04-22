<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useAuth } from '../composables/useAuth';

const props = defineProps({
    title: {
        type: String,
        default: 'Каталог',
    },
});

const { isAuthenticated, user, logout } = useAuth();

const adminEmail = computed(() => user.value?.email ?? '');

function handleLogout() {
    logout();
}
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-linear-to-b from-slate-100 via-white to-slate-100 text-slate-900">
        <header class="border-b border-slate-200 bg-white/85 backdrop-blur-md">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <Link href="/" class="text-lg font-semibold tracking-tight text-slate-900">Каталог товаров</Link>

                <nav class="flex items-center gap-2 sm:gap-3">
                    <Link
                        href="/"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                    >
                        Товары
                    </Link>

                    <template v-if="isAuthenticated">
                        <Link
                            href="/admin/products"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                        >
                            Управление товарами
                        </Link>

                        <button
                            type="button"
                            class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
                            @click="handleLogout"
                        >
                            Выйти
                        </button>
                    </template>

                    <Link
                        v-else
                        href="/login"
                        class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
                    >
                        Войти
                    </Link>
                </nav>
            </div>

            <div v-if="isAuthenticated && adminEmail" class="border-t border-slate-200 bg-slate-50/80">
                <div class="mx-auto w-full max-w-6xl px-4 py-2 text-xs text-slate-600 sm:px-6 lg:px-8">
                    Администратор: {{ adminEmail }}
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

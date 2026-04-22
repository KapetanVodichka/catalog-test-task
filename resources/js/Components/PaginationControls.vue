<script setup>
const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    lastPage: {
        type: Number,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['go']);

function goToPage(page) {
    if (props.loading) {
        return;
    }

    if (page < 1 || page > props.lastPage || page === props.currentPage) {
        return;
    }

    emit('go', page);
}
</script>

<template>
    <div v-if="lastPage > 1" class="mt-8 flex items-center justify-center gap-2">
        <button
            type="button"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="currentPage <= 1 || loading"
            @click="goToPage(currentPage - 1)"
        >
            Назад
        </button>

        <span class="min-w-28 text-center text-sm text-slate-600">
            Страница {{ currentPage }} из {{ lastPage }}
        </span>

        <button
            type="button"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="currentPage >= lastPage || loading"
            @click="goToPage(currentPage + 1)"
        >
            Далее
        </button>
    </div>
</template>

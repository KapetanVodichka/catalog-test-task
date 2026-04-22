<script setup>
const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    submitLabel: {
        type: String,
        default: 'Сохранить',
    },
});

const emit = defineEmits(['submit']);

function handleSubmit() {
    emit('submit');
}
</script>

<template>
    <form class="space-y-5" @submit.prevent="handleSubmit">
        <div class="space-y-1.5">
            <label for="name" class="block text-sm font-medium text-slate-800">Название</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                maxlength="255"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none ring-0 transition placeholder:text-slate-400 focus:border-slate-500"
                placeholder="Введите название товара"
            >
            <p v-if="errors.name" class="text-sm text-rose-600">{{ errors.name }}</p>
        </div>

        <div class="space-y-1.5">
            <label for="category" class="block text-sm font-medium text-slate-800">Категория</label>
            <select
                id="category"
                v-model="form.category_id"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
            >
                <option value="">Выберите категорию</option>
                <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                    {{ category.name }}
                </option>
            </select>
            <p v-if="errors.category_id" class="text-sm text-rose-600">{{ errors.category_id }}</p>
        </div>

        <div class="space-y-1.5">
            <label for="price" class="block text-sm font-medium text-slate-800">Цена</label>
            <input
                id="price"
                v-model="form.price"
                type="number"
                min="0.01"
                step="0.01"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none ring-0 transition focus:border-slate-500"
                placeholder="0.00"
            >
            <p v-if="errors.price" class="text-sm text-rose-600">{{ errors.price }}</p>
        </div>

        <div class="space-y-1.5">
            <label for="description" class="block text-sm font-medium text-slate-800">Описание</label>
            <textarea
                id="description"
                v-model="form.description"
                rows="5"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-slate-900 outline-none ring-0 transition placeholder:text-slate-400 focus:border-slate-500"
                placeholder="Кратко опишите товар"
            />
            <p v-if="errors.description" class="text-sm text-rose-600">{{ errors.description }}</p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="loading"
            >
                {{ loading ? 'Сохранение...' : submitLabel }}
            </button>
        </div>
    </form>
</template>

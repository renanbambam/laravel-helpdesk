<script setup>
import { reactive, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    tickets: { type: Object, required: true },
    filters: { type: Object, required: true },
    agents: { type: Array, required: true },
    priorityOptions: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    priority: props.filters.priority ?? '',
    agent_id: props.filters.agent_id ?? '',
});

const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

function applyFilters() {
    router.get('/chamados', {
        ...filters,
        sort: sort.value,
        direction: direction.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

let debounceTimer = null;
watch(filters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 300);
});

function sortBy(column) {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
    applyFilters();
}

const columns = [
    { key: 'title', label: 'Chamado' },
    { key: 'agent', label: 'Responsável', sortable: false },
    { key: 'priority', label: 'Prioridade' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Aberto em' },
];
</script>

<template>
    <Head title="Chamados" />

    <AppLayout>
        <div class="mb-6 flex items-end justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Chamados</h1>
                <p class="mt-1 text-sm text-slate-500">{{ tickets.total }} chamado(s) no total</p>
            </div>
        </div>

        <div class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            <input
                v-model="filters.search"
                type="search"
                placeholder="Buscar por título ou solicitante"
                class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <select v-model="filters.status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos os status</option>
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
            <select v-model="filters.priority" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas as prioridades</option>
                <option v-for="option in priorityOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
            </select>
            <select v-model="filters.agent_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos os responsáveis</option>
                <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                            :class="column.sortable === false ? '' : 'cursor-pointer select-none hover:text-slate-700'"
                            @click="column.sortable === false ? null : sortBy(column.key)"
                        >
                            {{ column.label }}
                            <span v-if="sort === column.key">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <Link :href="`/chamados/${ticket.id}`" class="font-medium text-slate-800 hover:text-indigo-600">
                                {{ ticket.title }}
                            </Link>
                            <p v-if="ticket.requester_name" class="text-xs text-slate-500">por {{ ticket.requester_name }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ ticket.agent?.name ?? '—' }}</td>
                        <td class="px-4 py-3"><PriorityBadge :priority="ticket.priority" /></td>
                        <td class="px-4 py-3"><StatusBadge :status="ticket.status" /></td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ ticket.created_at_label }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <Link :href="`/chamados/${ticket.id}/edit`" class="text-indigo-600 hover:underline">Editar</Link>
                        </td>
                    </tr>
                    <tr v-if="tickets.data.length === 0">
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                            Nenhum chamado encontrado com os filtros atuais.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-end">
            <Pagination :links="tickets.links" />
        </div>
    </AppLayout>
</template>

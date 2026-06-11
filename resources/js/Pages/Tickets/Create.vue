<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketForm from '@/Components/TicketForm.vue';

defineProps({
    agents: { type: Array, required: true },
    priorityOptions: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});
</script>

<template>
    <Head title="Novo chamado" />

    <AppLayout>
        <div class="mb-6">
            <Link href="/chamados" class="text-sm text-indigo-600 hover:underline">&larr; Voltar para a lista</Link>
            <h1 class="mt-2 text-2xl font-semibold text-slate-800">Abrir novo chamado</h1>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <TicketForm
                :agents="agents"
                :priority-options="priorityOptions"
                :status-options="statusOptions"
            />
        </div>
    </AppLayout>
</template>

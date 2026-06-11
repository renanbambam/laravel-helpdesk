<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketForm from '@/Components/TicketForm.vue';

const props = defineProps({
    ticket: { type: Object, required: true },
    agents: { type: Array, required: true },
    priorityOptions: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});

const ticket = props.ticket;
</script>

<template>
    <Head title="Editar chamado" />

    <AppLayout>
        <div class="mb-6">
            <Link :href="`/chamados/${ticket.id}`" class="text-sm text-indigo-600 hover:underline">
                &larr; Voltar para o chamado
            </Link>
            <h1 class="mt-2 text-2xl font-semibold text-slate-800">Editar chamado #{{ ticket.id }}</h1>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <TicketForm
                :ticket="ticket"
                :agents="agents"
                :priority-options="priorityOptions"
                :status-options="statusOptions"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PriorityBadge from '@/Components/PriorityBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    ticket: { type: Object, required: true },
});

function destroy() {
    if (confirm('Tem certeza que deseja remover este chamado?')) {
        router.delete(`/chamados/${props.ticket.id}`);
    }
}
</script>

<template>
    <Head :title="`Chamado #${ticket.id}`" />

    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <Link href="/chamados" class="text-sm text-indigo-600 hover:underline">&larr; Voltar para a lista</Link>
            <div class="flex items-center gap-2">
                <Link
                    :href="`/chamados/${ticket.id}/edit`"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Editar
                </Link>
                <button
                    type="button"
                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                    @click="destroy"
                >
                    Remover
                </button>
            </div>
        </div>

        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-slate-400">#{{ ticket.id }}</span>
                    <StatusBadge :status="ticket.status" />
                    <PriorityBadge :priority="ticket.priority" />
                </div>
                <h1 class="mt-2 text-2xl font-semibold text-slate-800">{{ ticket.title }}</h1>
            </div>

            <dl class="grid gap-6 px-6 py-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-slate-500">Descrição</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-800">{{ ticket.description }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Responsável</dt>
                    <dd class="mt-1 text-slate-800">{{ ticket.agent?.name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Solicitante</dt>
                    <dd class="mt-1 text-slate-800">{{ ticket.requester_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Aberto em</dt>
                    <dd class="mt-1 text-slate-800">{{ ticket.created_at_label }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-slate-500">Última atualização</dt>
                    <dd class="mt-1 text-slate-800">{{ ticket.updated_at_label }}</dd>
                </div>
            </dl>
        </article>
    </AppLayout>
</template>

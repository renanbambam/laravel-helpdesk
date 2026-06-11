<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    ticket: { type: Object, default: null },
    agents: { type: Array, required: true },
    priorityOptions: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
});

const isEdit = computed(() => props.ticket !== null);

const form = useForm({
    title: props.ticket?.title ?? '',
    description: props.ticket?.description ?? '',
    priority: props.ticket?.priority?.value ?? 'media',
    status: props.ticket?.status?.value ?? 'aberto',
    requester_name: props.ticket?.requester_name ?? '',
    assignment_mode: isEdit.value ? 'manual' : 'auto',
    agent_id: props.ticket?.agent_id ?? null,
});

function submit() {
    if (isEdit.value) {
        form.put(`/chamados/${props.ticket.id}`);
    } else {
        form.post('/chamados');
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div>
            <label for="title" class="block text-sm font-medium text-slate-700">Título</label>
            <input
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Ex.: Impressora não está funcionando"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-slate-700">Descrição</label>
            <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Descreva o problema ou a solicitação com detalhes"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="priority" class="block text-sm font-medium text-slate-700">Prioridade</label>
                <select
                    id="priority"
                    v-model="form.priority"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
            </div>
        </div>

        <div>
            <label for="requester_name" class="block text-sm font-medium text-slate-700">
                Solicitante <span class="text-slate-400">(opcional)</span>
            </label>
            <input
                id="requester_name"
                v-model="form.requester_name"
                type="text"
                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Nome de quem abriu o chamado"
            />
            <p v-if="form.errors.requester_name" class="mt-1 text-sm text-red-600">{{ form.errors.requester_name }}</p>
        </div>

        <fieldset class="rounded-lg border border-slate-200 p-4">
            <legend class="px-1 text-sm font-medium text-slate-700">Responsável pelo atendimento</legend>

            <div class="mt-2 space-y-3">
                <label class="flex items-start gap-3">
                    <input v-model="form.assignment_mode" type="radio" value="auto" class="mt-1 text-indigo-600 focus:ring-indigo-500" />
                    <span>
                        <span class="block text-sm font-medium text-slate-800">Distribuir automaticamente</span>
                        <span class="block text-sm text-slate-500">
                            Atribui ao responsável com menos chamados em aberto no momento.
                        </span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input v-model="form.assignment_mode" type="radio" value="manual" class="mt-1 text-indigo-600 focus:ring-indigo-500" />
                    <span class="w-full">
                        <span class="block text-sm font-medium text-slate-800">Escolher manualmente</span>
                        <select
                            v-model="form.agent_id"
                            :disabled="form.assignment_mode !== 'manual'"
                            class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-50 disabled:text-slate-400"
                        >
                            <option :value="null" disabled>Selecione um responsável</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                                {{ agent.name }} — {{ agent.open_tickets_count }} em aberto
                            </option>
                        </select>
                    </span>
                </label>
                <p v-if="form.errors.agent_id" class="text-sm text-red-600">{{ form.errors.agent_id }}</p>
            </div>
        </fieldset>

        <div class="flex items-center justify-end gap-3">
            <a href="/chamados" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800">
                Cancelar
            </a>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50"
            >
                {{ isEdit ? 'Salvar alterações' : 'Abrir chamado' }}
            </button>
        </div>
    </form>
</template>

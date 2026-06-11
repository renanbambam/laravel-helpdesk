<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: Object, required: true },
});

const classes = computed(() => {
    return {
        aberto: 'bg-blue-100 text-blue-700 ring-blue-200',
        em_andamento: 'bg-amber-100 text-amber-800 ring-amber-200',
        resolvido: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        fechado: 'bg-slate-200 text-slate-600 ring-slate-300',
    }[props.status.value] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
        :class="classes"
    >
        {{ status.label }}
    </span>
</template>

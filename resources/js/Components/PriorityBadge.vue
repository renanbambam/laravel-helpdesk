<script setup>
import { computed } from 'vue';

const props = defineProps({
    priority: { type: Object, required: true },
});

const classes = computed(() => {
    return {
        baixa: 'bg-slate-100 text-slate-700 ring-slate-200',
        media: 'bg-amber-100 text-amber-800 ring-amber-200',
        alta: 'bg-red-100 text-red-700 ring-red-200',
    }[props.priority.value] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset"
        :class="classes"
    >
        {{ priority.label }}
    </span>
</template>

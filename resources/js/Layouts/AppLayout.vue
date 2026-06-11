<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const appName = computed(() => page.props.appName ?? 'Chamados Internos');
const flash = computed(() => page.props.flash ?? {});
</script>

<template>
    <div class="min-h-full">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <Link href="/chamados" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                        CI
                    </span>
                    <span class="text-lg font-semibold text-slate-800">{{ appName }}</span>
                </Link>
                <Link
                    href="/chamados/create"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                >
                    + Novo chamado
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <div
                v-if="flash.success"
                class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
            >
                {{ flash.error }}
            </div>

            <slot />
        </main>
    </div>
</template>

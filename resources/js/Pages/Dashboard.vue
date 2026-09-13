<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TicketStatsCards from '@/Components/TicketStatsCards.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({ total: 0, open: 0, in_progress: 0, closed: 0, executed: 0 }) },
    globalStats: { type: Object, default: () => ({ total: 0, open: 0, in_progress: 0, closed: 0, executed: 0 }) },
    period: { type: String, default: '' },
    periodStart: { type: String, default: '' },
    periodEnd: { type: String, default: '' },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-zinc-100">
                Dashboard
            </h2>
        </template>

        <div class="py-6 space-y-6">
            <TicketStatsCards :stats="stats" :global-stats="globalStats" :period="period"
                :period-start="periodStart" :period-end="periodEnd" />
        </div>
    </AuthenticatedLayout>
</template>

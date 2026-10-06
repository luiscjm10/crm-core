<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TicketStatsCards from '@/Components/TicketStatsCards.vue';
import TicketTrendsChart from '@/Components/TicketTrendsChart.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({ total: 0, open: 0, in_progress: 0, closed: 0, executed: 0 }) },
    globalStats: { type: Object, default: () => ({ total: 0, open: 0, in_progress: 0, closed: 0, executed: 0 }) },
    period: { type: String, default: '' },
    periodStart: { type: String, default: '' },
    periodEnd: { type: String, default: '' },
});

const canViewTrends = computed(() =>
    usePage().props.auth.permissions?.includes('tickets.view-trends') ?? false
);
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
            <TicketTrendsChart v-if="canViewTrends" />
            <TicketStatsCards :stats="stats" :global-stats="globalStats" :period="period"
                :period-start="periodStart" :period-end="periodEnd" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TicketStatsCards from '@/Components/TicketStatsCards.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({ open: 0, in_progress: 0, resolved: 0, closed: 0 }) },
    period: { type: String, default: '' },
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
            <TicketStatsCards :stats="stats" :period="period" />
        </div>
    </AuthenticatedLayout>
</template>

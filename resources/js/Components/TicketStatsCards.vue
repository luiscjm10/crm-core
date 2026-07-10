<script setup>
import { Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps({
    stats: { type: Object, required: true },
    period: { type: String, required: true },
});

const items = [
    { key: 'open', label: 'Solicitudes abiertas', color: 'bg-blue-500', route: 'admin.tickets.index', params: { status: 'open' } },
    { key: 'in_progress', label: 'Solicitudes en progreso', color: 'bg-amber-500', route: 'admin.tickets.index', params: { status: 'in_progress' } },
    { key: 'resolved', label: 'Solicitudes resueltas', color: 'bg-emerald-500', route: 'admin.tickets.index', params: { status: 'resolved' } },
    { key: 'closed', label: 'Solicitudes cerradas', color: 'bg-zinc-400 dark:bg-zinc-600', route: 'admin.tickets.index', params: { status: 'closed' } },
];
</script>

<template>
    <Card size="sm">
        <CardContent>
            <p class="text-sm text-muted-foreground mb-4">Correspondiente a {{ period }}</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link v-for="item in items" :key="item.key"
                :href="route(item.route, item.params)"
                class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-zinc-800 p-4 hover:bg-gray-50 dark:hover:bg-zinc-900/50 transition-colors">
                <div class="flex items-center justify-center w-10 h-10 rounded-full text-white text-sm font-bold"
                    :class="item.color">
                    {{ stats[item.key] }}
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-zinc-300">{{ item.label }}</span>
            </Link>
            </div>
        </CardContent>
    </Card>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { CircleCheck, Clock3, Hammer, Inbox } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps({
    stats: { type: Object, required: true },
    globalStats: { type: Object, required: true },
    period: { type: String, required: true },
    periodStart: { type: String, required: true },
    periodEnd: { type: String, required: true },
});

const statusItems = [
    {
        key: 'open',
        label: 'Abiertas',
        icon: Inbox,
        iconClass: 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
    },
    {
        key: 'in_progress',
        label: 'En progreso',
        icon: Clock3,
        iconClass: 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
    },
    {
        key: 'closed',
        label: 'Cerradas',
        icon: CircleCheck,
        iconClass: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-400/15 dark:text-zinc-400',
    },
];

const executedItem = {
    key: 'executed',
    label: 'Ejecutadas',
    icon: Hammer,
    iconClass: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
};

const monthParams = (status) => ({
    status,
    from: 'dashboard',
    date_from: props.periodStart,
    date_to: props.periodEnd,
});

const executedTileHref = (global) =>
    route('admin.tickets.index', global
        ? { from: 'dashboard', date_field: 'executed_at' }
        : {
            from: 'dashboard',
            date_field: 'executed_at',
            date_from: props.periodStart,
            date_to: props.periodEnd,
        });
</script>

<template>
    <div class="space-y-6">
        <div class="grid gap-6">
            <Card>
                <CardHeader class="flex flex-row items-start justify-between gap-4">
                    <div>
                        <CardTitle>Solicitudes del mes</CardTitle>
                        <CardDescription>Solicitadas en {{ period }}</CardDescription>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full border border-emerald-600/30 bg-emerald-600/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-400">
                        {{ stats.total }} total
                    </span>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
                        <Link v-for="item in statusItems" :key="item.key"
                            :href="route('admin.tickets.index', monthParams(item.key))"
                            class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-zinc-800 p-4 transition-colors hover:bg-gray-50 dark:hover:bg-zinc-900/50">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                                :class="item.iconClass">
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-tight text-gray-900 dark:text-zinc-100">
                                    {{ stats[item.key] }}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">{{ item.label }}</p>
                            </div>
                        </Link>
                        <Link :href="executedTileHref(false)"
                            class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-zinc-800 p-4 transition-colors hover:bg-gray-50 dark:hover:bg-zinc-900/50">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                                :class="executedItem.iconClass">
                                <component :is="executedItem.icon" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-tight text-gray-900 dark:text-zinc-100">
                                    {{ stats.executed }}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">{{ executedItem.label }}</p>
                            </div>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-start justify-between gap-4">
                    <div>
                        <CardTitle>Histórico</CardTitle>
                        <CardDescription>Sin filtro de fechas</CardDescription>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full border border-emerald-600/30 bg-emerald-600/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-400">
                        {{ globalStats.total }} total
                    </span>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 grid-cols-2 lg:grid-cols-4">
                        <Link v-for="item in statusItems" :key="item.key"
                            :href="route('admin.tickets.index', { status: item.key, from: 'dashboard' })"
                            class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-zinc-800 p-4 transition-colors hover:bg-gray-50 dark:hover:bg-zinc-900/50">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                                :class="item.iconClass">
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-tight text-gray-900 dark:text-zinc-100">
                                    {{ globalStats[item.key] }}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">{{ item.label }}</p>
                            </div>
                        </Link>
                        <Link :href="executedTileHref(true)"
                            class="flex items-center gap-4 rounded-lg border border-gray-200 dark:border-zinc-800 p-4 transition-colors hover:bg-gray-50 dark:hover:bg-zinc-900/50">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                                :class="executedItem.iconClass">
                                <component :is="executedItem.icon" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-2xl font-bold leading-tight text-gray-900 dark:text-zinc-100">
                                    {{ globalStats.executed }}
                                </p>
                                <p class="truncate text-xs text-muted-foreground">{{ executedItem.label }}</p>
                            </div>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
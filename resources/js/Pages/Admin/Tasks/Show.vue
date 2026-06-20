<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Flag, MapPin, Wrench, Code } from '@lucide/vue';
import { useDark } from '@vueuse/core';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const props = defineProps({
    task: Object,
    company: Object,
});

const isDark = useDark();

const taskPerms = computed(() => usePage().props.auth.permissions ?? []);
const canUpdateTask = computed(() => taskPerms.value.includes('tasks.update'));
const canCompleteTask = computed(() => taskPerms.value.includes('tasks.complete'));

const typeIcons = {
    general: Flag,
    visit: MapPin,
    maintenance: Wrench,
    development: Code,
};

const typeLabels = {
    general: 'General',
    visit: 'Visita',
    maintenance: 'Mantenimiento',
    development: 'Desarrollo',
};

const priorityLabels = {
    low: 'Baja',
    medium: 'Media',
    high: 'Alta',
    urgent: 'Urgente',
};

const statusLabels = {
    planned: 'Planeada',
    todo: 'Por hacer',
    in_progress: 'En progreso',
    done: 'Completada',
    cancelled: 'Cancelada',
};

const intervalLabels = {
    daily: 'Diario',
    weekly: 'Semanal',
    biweekly: 'Quincenal',
    monthly: 'Mensual',
    quarterly: 'Trimestral',
    'semi-annually': 'Semestral',
    annually: 'Anual',
};

const statusColors = {
    planned: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    todo: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    in_progress: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    done: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
};

const priorityColors = {
    low: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    medium: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    high: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    urgent: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
};

const canComplete = !['done', 'cancelled'].includes(props.task.status);

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const completeTask = () => {
    router.patch(route('admin.companies.tasks.complete', [props.company.id, props.task.id]));
};
</script>

<template>
    <Head :title="`Tarea - ${task.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.tasks.index', { company_id: props.company.id })"
                    class="text-gray-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors flex items-center justify-center h-8 w-8 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </Link>
                <span>Detalle de Tarea</span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader
                class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6 flex flex-row items-start justify-between gap-4">
                <div class="min-w-0">
                    <CardTitle class="text-xl break-words">{{ task.name }}</CardTitle>
                    <CardDescription>Información general de la tarea.</CardDescription>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <Button v-if="canComplete && canCompleteTask" @click="completeTask"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-sm">
                        Completar
                    </Button>
                    <Button v-if="canUpdateTask" variant="outline" as-child
                        class="border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800">
                        <Link :href="route('admin.companies.tasks.edit', [props.company.id, props.task.id])">Editar</Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Estado</p>
                        <p class="text-base">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium"
                                :class="statusColors[task.status] || statusColors.planned">
                                {{ statusLabels[task.status] || task.status }}
                            </span>
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Tipo</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <component :is="typeIcons[task.type]" class="h-4 w-4" />
                                {{ typeLabels[task.type] || task.type }}
                            </span>
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Prioridad</p>
                        <p class="text-base">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium"
                                :class="priorityColors[task.priority] || ''">
                                {{ priorityLabels[task.priority] || task.priority }}
                            </span>
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Fecha de vencimiento</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatDate(task.due_date) }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Responsable</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{ task.assigned_user?.name || 'Sin asignar' }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Creado por</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{ task.creator?.name || '—' }}
                        </p>
                    </div>

                    <div class="space-y-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Descripción</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 whitespace-pre-wrap">
                            {{ task.description || 'Sin descripción' }}
                        </p>
                    </div>

                    <div class="space-y-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Recurrencia</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            <template v-if="task.is_recurring && task.recurrence_interval">
                                Tarea recurrente — {{ intervalLabels[task.recurrence_interval] || task.recurrence_interval }}
                            </template>
                            <template v-else>
                                No recurrente
                            </template>
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Creada</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatDate(task.created_at) }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Actualizada</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatDate(task.updated_at) }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

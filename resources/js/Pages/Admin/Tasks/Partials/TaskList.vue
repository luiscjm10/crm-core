<script setup>
import { Link } from '@inertiajs/vue3';
import { Flag, MapPin, Wrench, Code } from '@lucide/vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps({
    tasks: Object,
    currentCompanyId: String,
    canUpdate: { type: Boolean, default: false },
    canDelete: { type: Boolean, default: false },
    canComplete: { type: Boolean, default: false },
});

const emit = defineEmits(['open-task', 'complete-task', 'delete-task']);

const typeIcons = {
    general: Flag,
    visit: MapPin,
    maintenance: Wrench,
    development: Code,
};

const statusLabels = {
    planned: 'Planeadas',
    todo: 'Por hacer',
    in_progress: 'En progreso',
    done: 'Completadas',
    cancelled: 'Canceladas',
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

const priorityColors = {
    low: 'text-zinc-500 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300',
    medium: 'text-blue-500 bg-blue-100 dark:bg-blue-900 dark:text-blue-300',
    high: 'text-amber-500 bg-amber-100 dark:bg-amber-900 dark:text-amber-300',
    urgent: 'text-red-500 bg-red-100 dark:bg-red-900 dark:text-red-300',
};

const statusColors = {
    planned: 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300',
    todo: 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
    in_progress: 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300',
    done: 'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300',
    cancelled: 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
};

const formatDate = (date) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const isOverdue = (task) => {
    if (!task.due_date || task.status === 'done' || task.status === 'cancelled') return false;
    return new Date(task.due_date) < new Date();
};

import { computed } from 'vue';

const canComplete = (task) =>
    !['done', 'cancelled'].includes(task.status);

const showCompany = computed(() => props.currentCompanyId === 'all');
</script>

<template>
    <div class="mt-6">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nombre</TableHead>
                    <TableHead v-if="showCompany">Compañía</TableHead>
                    <TableHead>Tipo</TableHead>
                    <TableHead>Prioridad</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead>Vencimiento</TableHead>
                    <TableHead>Responsable</TableHead>
                    <TableHead class="text-right">Acciones</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="task in tasks.data" :key="task.id"
                    class="cursor-pointer" @click="emit('open-task', task)">
                    <TableCell class="font-medium">
                        <div class="flex items-center gap-2">
                            <component :is="typeIcons[task.type]" class="h-4 w-4 shrink-0 text-muted-foreground" />
                            {{ task.name }}
                        </div>
                    </TableCell>
                    <TableCell v-if="showCompany">{{ task.company?.name }}</TableCell>
                    <TableCell>
                        <div class="flex items-center gap-1.5">
                            <component :is="typeIcons[task.type]" class="h-3.5 w-3.5 text-muted-foreground" />
                            <span class="text-sm">{{ typeLabels[task.type] || task.type }}</span>
                        </div>
                    </TableCell>
                    <TableCell>
                        <span class="inline-flex items-center text-xs font-medium rounded-full px-2.5 py-0.5"
                            :class="priorityColors[task.priority] || ''">
                            {{ priorityLabels[task.priority] || task.priority }}
                        </span>
                    </TableCell>
                    <TableCell>
                        <span class="inline-flex items-center text-xs font-medium rounded-full px-2.5 py-0.5"
                            :class="statusColors[task.status] || ''">
                            {{ statusLabels[task.status] || task.status }}
                        </span>
                    </TableCell>
                    <TableCell>
                        <span v-if="task.due_date" :class="isOverdue(task) ? 'text-red-500 dark:text-red-400 font-medium' : 'text-muted-foreground'">
                            {{ isOverdue(task) ? '\u26A0\uFE0F ' : '' }}{{ formatDate(task.due_date) }}
                        </span>
                        <span v-else class="text-muted-foreground">—</span>
                    </TableCell>
                    <TableCell>
                        <span v-if="task.assigned_user" class="text-sm">{{ task.assigned_user.name }}</span>
                        <span v-else class="text-muted-foreground">—</span>
                    </TableCell>
                    <TableCell class="text-right">
                        <div class="flex items-center justify-end gap-2" @click.stop>
                            <button v-if="canComplete(task) && props.canComplete" @click="emit('complete-task', task)"
                                class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium transition-colors">
                                Completar
                            </button>
                            <Link v-if="props.canUpdate" :href="route('admin.companies.tasks.edit', [task.company_id, task.id])"
                                class="text-xs text-primary hover:text-primary/80 font-medium transition-colors">
                                Editar
                            </Link>
                            <button v-if="props.canDelete" @click="emit('delete-task', task)"
                                class="text-xs text-destructive hover:text-destructive/80 font-medium transition-colors">
                                Eliminar
                            </button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>

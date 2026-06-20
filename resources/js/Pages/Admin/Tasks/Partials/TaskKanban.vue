<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Flag, MapPin, Wrench, Code } from '@lucide/vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { VueDraggable as draggable } from 'vue-draggable-plus';

const props = defineProps({
    tasks: Object,
    statuses: Array,
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

const statusIcons = {
    planned: 'bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600',
    todo: 'bg-blue-50 dark:bg-blue-950 border-blue-300 dark:border-blue-700',
    in_progress: 'bg-amber-50 dark:bg-amber-950 border-amber-300 dark:border-amber-700',
    done: 'bg-emerald-50 dark:bg-emerald-950 border-emerald-300 dark:border-emerald-700',
    cancelled: 'bg-red-50 dark:bg-red-950 border-red-300 dark:border-red-700',
};

const statusHeaderColors = {
    planned: 'text-zinc-600 dark:text-zinc-400',
    todo: 'text-blue-600 dark:text-blue-400',
    in_progress: 'text-amber-600 dark:text-amber-400',
    done: 'text-emerald-600 dark:text-emerald-400',
    cancelled: 'text-red-600 dark:text-red-400',
};

const columns = computed(() =>
    props.statuses.map((value) => ({
        value,
        label: statusLabels[value] || value,
    }))
);

const tasksByCol = ref({});

const buildTasksByCol = () => {
    const map = {};
    props.statuses.forEach((s) => { map[s] = []; });
    props.tasks.data.forEach((task) => {
        if (map[task.status]) map[task.status].push(task);
    });
    tasksByCol.value = map;
};

buildTasksByCol();

watch(() => props.tasks, () => buildTasksByCol(), { deep: true });

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

const canComplete = (task) =>
    !['done', 'cancelled'].includes(task.status);

const dragOptions = {
    group: 'tasks',
    animation: 200,
    ghostClass: 'opacity-30',
    filter: '.empty-state',
};

const handleDragEnd = (evt) => {
    const taskId = evt.item.dataset.taskId;
    const companyId = evt.item.dataset.companyId;
    const columnEl = evt.to.closest('[data-status]');

    if (!columnEl || !taskId) return;

    const newStatus = columnEl.dataset.status;

    router.patch(route('admin.companies.tasks.update-status', [companyId, taskId]), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="flex gap-4 overflow-x-auto pb-4 mt-6" style="min-height: 60vh;">
        <div v-for="col in columns" :key="col.value"
            :data-status="col.value"
            class="flex-shrink-0 w-72 flex flex-col">
            <div class="flex items-center gap-2 mb-3 px-1">
                <span class="text-sm font-semibold uppercase tracking-wider" :class="statusHeaderColors[col.value]">
                    {{ col.label }}
                </span>
                <span class="text-xs font-medium text-muted-foreground bg-muted rounded-full px-2 py-0.5">
                    {{ tasksByCol[col.value]?.length ?? 0 }}
                </span>
            </div>

            <draggable
                v-model="tasksByCol[col.value]"
                tag="div"
                class="space-y-3 flex-1 min-h-[6rem]"
                group="tasks"
                animation="200"
                ghost-class="opacity-30"
                @end="handleDragEnd"
            >
                <div v-if="tasksByCol[col.value]?.length === 0"
                    class="flex items-center justify-center h-24 border-2 border-dashed border-gray-200 dark:border-zinc-700 rounded-xl empty-state">
                    <p class="text-xs text-muted-foreground">Sin tareas</p>
                </div>
                <div v-for="task in tasksByCol[col.value]" :key="task.id"
                    :data-task-id="task.id" :data-company-id="task.company_id"
                    @click.stop="(e) => emit('open-task', task, e)" class="cursor-pointer">
                    <Card :class="[statusIcons[task.status] || statusIcons[col.value], 'border-2',
                        task.priority === 'urgent' ? 'border-l-red-500' : task.priority === 'high' ? 'border-l-amber-500' : '']">
                        <CardHeader class="p-3 pb-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <component :is="typeIcons[task.type]" class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                    <span
                                        class="font-medium text-sm text-foreground hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors leading-tight truncate">
                                        {{ task.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <span v-if="task.priority === 'urgent'" class="text-xs text-red-500" title="Urgente">&#x26A0;&#xFE0F;</span>
                                    <span v-if="task.is_recurring" class="text-xs text-muted-foreground" title="Tarea recurrente">
                                        &#x21bb;
                                    </span>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="p-3 pt-2 space-y-2">
                            <div v-if="task.due_date" class="flex items-center gap-1.5">
                                <span class="text-xs" :class="isOverdue(task) ? 'text-red-500 dark:text-red-400 font-medium' : 'text-muted-foreground'">
                                    {{ isOverdue(task) ? '\u26A0\uFE0F' : '\uD83D\uDCC5' }}
                                    {{ formatDate(task.due_date) }}
                                </span>
                            </div>
                            <div v-if="task.assigned_user" class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-full px-2 py-0.5">
                                    {{ task.assigned_user.name }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <button v-if="canComplete(task) && props.canComplete" @click.stop="emit('complete-task', task)"
                                    class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium transition-colors">
                                    Completar
                                </button>
                                <Link v-if="props.canUpdate" :href="route('admin.companies.tasks.edit', [task.company_id, task.id])"
                                    @click.stop
                                    class="text-xs text-primary hover:text-primary/80 font-medium transition-colors">
                                    Editar
                                </Link>
                                <button v-if="props.canDelete" @click.stop="emit('delete-task', task)"
                                    class="text-xs text-destructive hover:text-destructive/80 font-medium transition-colors">
                                    Eliminar
                                </button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </draggable>
</div>
</div>
</template>

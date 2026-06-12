<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { useDark } from '@vueuse/core';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Flag, MapPin, Wrench, Code } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import TaskKanban from './Partials/TaskKanban.vue';
import TaskList from './Partials/TaskList.vue';
import TaskCalendar from './Partials/TaskCalendar.vue';

const props = defineProps({
    tasks: Object,
    statuses: Array,
    types: Array,
    priorities: Array,
    companies: Array,
    currentCompanyId: String,
});

const isDark = useDark();
const viewMode = ref('kanban');

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

const priorityColors = {
    low: 'text-zinc-500 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-300',
    medium: 'text-blue-500 bg-blue-100 dark:bg-blue-900 dark:text-blue-300',
    high: 'text-amber-500 bg-amber-100 dark:bg-amber-900 dark:text-amber-300',
    urgent: 'text-red-500 bg-red-100 dark:bg-red-900 dark:text-red-300',
};

const statusLabels = {
    planned: 'Planeadas',
    todo: 'Por hacer',
    in_progress: 'En progreso',
    done: 'Completadas',
    cancelled: 'Canceladas',
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

const canComplete = (task) =>
    !['done', 'cancelled'].includes(task.status);

const selectedTask = ref(null);
const isModalOpen = ref(false);
const isCompanyDialogOpen = ref(false);
const selectedCompanyForCreate = ref('');

const openTaskModal = (task, event) => {
    if (event?.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        document.documentElement.style.setProperty('--click-x', `${x}px`);
        document.documentElement.style.setProperty('--click-y', `${y}px`);
    }
    selectedTask.value = task;
    isModalOpen.value = true;
};

const completeTask = (task) => {
    isModalOpen.value = false;
    Swal.fire({
        title: '¿Completar tarea?',
        text: `Se marcará "${task.name}" como completada.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: isDark.value ? '#3f3f46' : '#e5e7eb',
        confirmButtonText: 'Sí, completar',
        cancelButtonText: 'Cancelar',
        background: isDark.value ? '#09090b' : '#ffffff',
        color: isDark.value ? '#fafafa' : '#111827',
        customClass: {
            popup: isDark.value ? 'border border-zinc-800 rounded-xl' : 'border border-gray-200 shadow-xl rounded-xl',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('admin.companies.tasks.complete', [task.company_id, task.id]), {}, {
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Completada!',
                        text: 'La tarea ha sido marcada como completada.',
                        icon: 'success',
                        background: isDark.value ? '#09090b' : '#ffffff',
                        color: isDark.value ? '#fafafa' : '#111827',
                        confirmButtonColor: '#10b981',
                    });
                }
            });
        }
    });
};

const deleteTask = (task) => {
    isModalOpen.value = false;
    Swal.fire({
        title: '¿Eliminar tarea?',
        text: `Se eliminará "${task.name}" permanentemente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: isDark.value ? '#3f3f46' : '#e5e7eb',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: isDark.value ? '#09090b' : '#ffffff',
        color: isDark.value ? '#fafafa' : '#111827',
        customClass: {
            popup: isDark.value ? 'border border-zinc-800 rounded-xl' : 'border border-gray-200 shadow-xl rounded-xl',
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.companies.tasks.destroy', [task.company_id, task.id]), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminada',
                        text: 'La tarea ha sido borrada.',
                        icon: 'success',
                        background: isDark.value ? '#09090b' : '#ffffff',
                        color: isDark.value ? '#fafafa' : '#111827',
                        confirmButtonColor: '#10b981',
                    });
                }
            });
        }
    });
};

const switchCompany = (companyId) => {
    router.get(route('admin.tasks.index'), { company_id: companyId }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleCreateClick = () => {
    if (props.currentCompanyId !== 'all') {
        router.get(route('admin.companies.tasks.create', props.currentCompanyId));
        return;
    }
    isCompanyDialogOpen.value = true;
};

const goToCreate = () => {
    if (selectedCompanyForCreate.value) {
        isCompanyDialogOpen.value = false;
        router.get(route('admin.companies.tasks.create', selectedCompanyForCreate.value));
    }
};

const viewComponents = {
    kanban: TaskKanban,
    list: TaskList,
    calendar: TaskCalendar,
};
</script>

<template>
    <Head title="Tareas" />

    <AuthenticatedLayout>
        <template #header>
            <span>Tareas</span>
        </template>

        <!-- Toolbar -->
        <div class="flex justify-between items-center flex-wrap gap-3 mt-6 mb-4">
            <div class="flex items-center gap-1 bg-muted rounded-lg p-0.5">
                <button @click="viewMode = 'kanban'"
                    :class="[viewMode === 'kanban' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground', 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all']">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z" />
                    </svg>
                    Kanban
                </button>
                <button @click="viewMode = 'list'"
                    :class="[viewMode === 'list' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground', 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all']">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Lista
                </button>
                <button @click="viewMode = 'calendar'"
                    :class="[viewMode === 'calendar' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground', 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-all']">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Calendario
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <select @change="switchCompany($event.target.value)"
                        class="text-sm bg-transparent border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 rounded-lg pl-3 pr-8 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer appearance-none">
                        <option value="all" :selected="currentCompanyId === 'all'">Todas las compañías</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id" :selected="currentCompanyId === String(c.id)">
                            {{ c.name }}
                        </option>
                    </select>
                    <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <Button @click="handleCreateClick"
                    class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 dark:text-zinc-950 transition-colors">
                    + Nueva Tarea
                </Button>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="!tasks.data || tasks.data.length === 0"
            class="flex flex-col items-center justify-center text-center">
            <p class="text-lg text-muted-foreground mb-4">No hay tareas registradas aún.</p>
            <Button @click="handleCreateClick"
                class="bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 dark:text-zinc-950">
                + Crear primera tarea
            </Button>
        </div>

        <!-- View content -->
        <component :is="viewComponents[viewMode]"
            v-else
            :tasks="tasks"
            :statuses="statuses"
            :types="types"
            :priorities="priorities"
            :current-company-id="currentCompanyId"
            @open-task="openTaskModal"
            @complete-task="completeTask"
            @delete-task="deleteTask"
        />

        <!-- Company selector dialog (for 'all' mode create) -->
        <Dialog v-model:open="isCompanyDialogOpen">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Seleccionar compañía</DialogTitle>
                    <DialogDescription>Elige la compañía para la nueva tarea.</DialogDescription>
                </DialogHeader>
                <div class="py-3">
                    <select v-model="selectedCompanyForCreate"
                        class="w-full rounded-md border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="" disabled>Seleccionar...</option>
                        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="ghost">Cancelar</Button>
                    </DialogClose>
                    <Button @click="goToCreate" :disabled="!selectedCompanyForCreate"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 dark:text-zinc-950">
                        Ir a crear
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Task Detail Modal -->
        <Dialog v-model:open="isModalOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle class="text-lg">{{ selectedTask?.name }}</DialogTitle>
                    <DialogDescription v-if="selectedTask?.description" class="mt-1">
                        {{ selectedTask.description }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-3 py-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Estado</span>
                        <span class="inline-flex items-center text-xs font-medium rounded-full px-2.5 py-0.5"
                            :class="{
                                'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300': selectedTask?.status === 'planned',
                                'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300': selectedTask?.status === 'todo',
                                'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300': selectedTask?.status === 'in_progress',
                                'bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300': selectedTask?.status === 'done',
                                'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300': selectedTask?.status === 'cancelled',
                            }">
                            {{ statusLabels[selectedTask?.status] || selectedTask?.status }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Tipo</span>
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium">
                            <component :is="typeIcons[selectedTask?.type]" class="h-4 w-4" />
                            {{ typeLabels[selectedTask?.type] || selectedTask?.type }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Prioridad</span>
                        <span class="inline-flex items-center text-xs font-medium rounded-full px-2.5 py-0.5"
                            :class="priorityColors[selectedTask?.priority] || ''">
                            {{ priorityLabels[selectedTask?.priority] || selectedTask?.priority }}
                        </span>
                    </div>

                    <div v-if="currentCompanyId === 'all' && selectedTask?.company" class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Compañía</span>
                        <span class="text-sm font-medium">{{ selectedTask.company.name }}</span>
                    </div>

                    <div v-if="selectedTask?.due_date" class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Vencimiento</span>
                        <span class="text-sm" :class="isOverdue(selectedTask) ? 'text-red-500 dark:text-red-400 font-medium' : ''">
                            {{ isOverdue(selectedTask) ? '\u26A0\uFE0F ' : '' }}
                            {{ formatDate(selectedTask.due_date) }}
                        </span>
                    </div>

                    <div v-if="selectedTask?.assigned_user" class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Responsable</span>
                        <span class="inline-flex items-center gap-1 text-sm bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-full px-2.5 py-0.5">
                            {{ selectedTask.assigned_user.name }}
                        </span>
                    </div>

                    <div v-if="selectedTask?.is_recurring" class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground w-24 shrink-0">Recurrente</span>
                        <span class="text-sm text-muted-foreground">&#x21bb; Sí</span>
                    </div>
                </div>

                <DialogFooter>
                    <Button v-if="canComplete(selectedTask)" @click="completeTask(selectedTask)"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-600 dark:hover:bg-emerald-500 dark:text-zinc-950">
                        Completar
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="route('admin.companies.tasks.edit', [selectedTask?.company_id, selectedTask?.id])">
                            Editar
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteTask(selectedTask)">
                        Eliminar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>

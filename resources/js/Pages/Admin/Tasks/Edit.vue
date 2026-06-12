<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    task: Object,
    users: Array,
    statuses: Array,
    types: Array,
    priorities: Array,
    company: Object,
});

const statusLabels = {
    planned: 'Planeada',
    todo: 'Por hacer',
    in_progress: 'En progreso',
    done: 'Completada',
    cancelled: 'Cancelada',
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

const form = useForm({
    name: props.task.name || '',
    description: props.task.description || '',
    status: props.task.status || 'planned',
    type: props.task.type || 'general',
    priority: props.task.priority || 'medium',
    due_date: props.task.due_date || '',
    assigned_user_id: props.task.assigned_user_id?.toString() || '',
    is_recurring: props.task.is_recurring || false,
    recurrence_interval: props.task.recurrence_interval || '',
});

const submit = () => {
    form.put(route('admin.companies.tasks.update', [props.company.id, props.task.id]));
};
</script>

<template>
    <Head :title="`Editar Tarea - ${task.name}`" />

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
                <span>Editar Tarea: <span class="text-gray-500 dark:text-zinc-400 font-normal">{{ task.name }}</span></span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6">
                <CardTitle>Actualizar Datos</CardTitle>
                <CardDescription>Modifica la información de la tarea.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <Label for="name">Nombre <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text" required placeholder="Título de la tarea" />
                            <p class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <Label for="description">Descripción</Label>
                            <textarea id="description" v-model="form.description"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 min-h-[100px]"
                                placeholder="Descripción detallada (opcional)"></textarea>
                            <p class="text-sm text-red-500" v-if="form.errors.description">{{ form.errors.description }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Estado</Label>
                            <select id="status" v-model="form.status"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabels[s] || s }}</option>
                            </select>
                            <p class="text-sm text-red-500" v-if="form.errors.status">{{ form.errors.status }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Tipo de Tarea</Label>
                            <select id="type" v-model="form.type"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <option v-for="t in types" :key="t" :value="t">{{ typeLabels[t] || t }}</option>
                            </select>
                            <p class="text-sm text-red-500" v-if="form.errors.type">{{ form.errors.type }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="priority">Prioridad</Label>
                            <select id="priority" v-model="form.priority"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <option v-for="p in priorities" :key="p" :value="p">{{ priorityLabels[p] || p }}</option>
                            </select>
                            <p class="text-sm text-red-500" v-if="form.errors.priority">{{ form.errors.priority }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="due_date">Fecha de vencimiento</Label>
                            <Input id="due_date" v-model="form.due_date" type="date" />
                            <p class="text-sm text-red-500" v-if="form.errors.due_date">{{ form.errors.due_date }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="assigned_user_id">Responsable</Label>
                            <select id="assigned_user_id" v-model="form.assigned_user_id"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <option value="">Sin asignar...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                            </select>
                            <p class="text-sm text-red-500" v-if="form.errors.assigned_user_id">{{ form.errors.assigned_user_id }}</p>
                        </div>

                        <div class="space-y-2 flex flex-col justify-end">
                            <div class="flex items-center gap-2 pt-2">
                                <input id="is_recurring" type="checkbox" v-model="form.is_recurring"
                                    class="rounded border-gray-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500 h-4 w-4" />
                                <Label for="is_recurring" class="cursor-pointer">Tarea recurrente</Label>
                            </div>
                            <p class="text-sm text-red-500" v-if="form.errors.is_recurring">{{ form.errors.is_recurring }}</p>

                            <div v-if="form.is_recurring" class="mt-2 space-y-2">
                                <Label for="recurrence_interval">Intervalo de recurrencia</Label>
                                <select id="recurrence_interval" v-model="form.recurrence_interval"
                                    class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                    <option value="">Seleccionar intervalo...</option>
                                    <option value="daily">Diario</option>
                                    <option value="weekly">Semanal</option>
                                    <option value="biweekly">Quincenal</option>
                                    <option value="monthly">Mensual</option>
                                    <option value="quarterly">Trimestral</option>
                                    <option value="semi-annually">Semestral</option>
                                    <option value="annually">Anual</option>
                                </select>
                                <p class="text-sm text-red-500" v-if="form.errors.recurrence_interval">{{ form.errors.recurrence_interval }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                        <Button type="button" variant="ghost" as-child>
                            <Link :href="route('admin.tasks.index', { company_id: props.company.id })">Cancelar</Link>
                        </Button>
                        <Button type="submit"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                            :disabled="form.processing">
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Tarea' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

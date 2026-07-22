<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed } from 'vue';
import { useDark } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

const props = defineProps({
    taskTypes: Object,
});

const perPage = ref(props.taskTypes?.per_page ?? 10);
const isDark = useDark();
const permissions = computed(() => usePage().props.auth.permissions ?? []);

const canCreate = computed(() => permissions.value.includes('task-types.create'));
const canUpdate = computed(() => permissions.value.includes('task-types.update'));
const canDelete = computed(() => permissions.value.includes('task-types.delete'));

const dialogOpen = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    description: '',
    is_active: true,
});

const openCreate = () => {
    editingType.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    dialogOpen.value = true;
};

const openEdit = (taskType) => {
    editingType.value = taskType;
    form.reset();
    form.clearErrors();
    form.name = taskType.name;
    form.description = taskType.description ?? '';
    form.is_active = taskType.is_active;
    dialogOpen.value = true;
};

const submit = () => {
    if (editingType.value) {
        form.put(route('admin.task-types.update', editingType.value.id), {
            onSuccess: () => {
                dialogOpen.value = false;
                editingType.value = null;
            },
        });
    } else {
        form.post(route('admin.task-types.store'), {
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
};

const changePerPage = () => {
    router.get(route('admin.task-types.index'), { perPage: perPage.value }, { preserveState: true, replace: true });
};

const confirmDelete = (taskType) => {
    Swal.fire({
        title: '¿Eliminar tipo?',
        text: `Se eliminará "${taskType.name}" permanentemente.`,
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
            router.delete(route('admin.task-types.destroy', taskType.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El tipo de tarea ha sido borrado.',
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
</script>

<template>
    <Head title="Tipos de Tarea" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Tipos de Tarea</span>
                <Button v-if="canCreate" variant="outline" @click="openCreate"
                    class="h-9 px-4 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-colors">
                    + Nuevo Tipo
                </Button>
            </div>
        </template>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Administrar Tipos de Tarea</CardTitle>
                <CardDescription>Define los tipos de tareas disponibles en el sistema.</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nombre</TableHead>
                            <TableHead>Descripción</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="taskTypes.data.length === 0">
                            <TableCell colspan="4" class="text-center text-muted-foreground h-24">
                                No hay tipos de tarea registrados aún.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="type in taskTypes.data" :key="type.id" v-else>
                            <TableCell class="font-medium text-foreground break-words whitespace-normal">{{ type.name }}</TableCell>
                            <TableCell class="text-muted-foreground break-words whitespace-normal">{{ type.description || '—' }}</TableCell>
                            <TableCell>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    :class="type.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'">
                                    {{ type.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </TableCell>
                            <TableCell class="text-right space-x-3">
                                <button v-if="canUpdate" @click="openEdit(type)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors text-sm">
                                    Editar
                                </button>
                                <button v-if="canDelete" @click="confirmDelete(type)"
                                    class="text-destructive hover:text-destructive/80 font-medium transition-colors text-sm">
                                    Eliminar
                                </button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="taskTypes.total" class="flex items-center justify-between mt-6">
                    <div class="flex items-center gap-2">
                        <label for="perPage" class="text-sm text-gray-500 dark:text-zinc-400">Registros por página:</label>
                        <select id="perPage" v-model="perPage" @change="changePerPage"
                            class="text-sm border border-gray-300 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-300 px-2 pr-7 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">
                        Mostrando <span class="font-medium">{{ taskTypes.from }}</span> a <span class="font-medium">{{ taskTypes.to }}</span> de <span class="font-medium">{{ taskTypes.total }}</span> registros
                    </p>
                    <div v-if="taskTypes.links?.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, i) in taskTypes.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors"
                                :class="link.active ? 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-zinc-950 font-semibold' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                                <span v-html="link.label" />
                            </Link>
                            <span v-else class="px-3 py-1.5 text-sm rounded-md text-gray-400 dark:text-zinc-600"
                                :class="{ 'cursor-not-allowed': i === 0 || i === taskTypes.links.length - 1 }"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingType ? 'Editar Tipo' : 'Nuevo Tipo' }}</DialogTitle>
                    <DialogDescription>
                        {{ editingType ? 'Modifica los datos del tipo de tarea.' : 'Ingresa los datos del nuevo tipo de tarea.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 py-2">
                    <div class="space-y-2">
                        <Label for="name">Nombre <span class="text-red-500">*</span></Label>
                        <input id="name" v-model="form.name" required
                            class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 placeholder-gray-400 dark:placeholder-zinc-500" />
                        <p class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Descripción</Label>
                        <textarea id="description" v-model="form.description" rows="3" spellcheck="true" lang="es-ES"
                            class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 placeholder-gray-400 dark:placeholder-zinc-500 resize-y"></textarea>
                        <p class="text-sm text-red-500" v-if="form.errors.description">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" type="checkbox" v-model="form.is_active"
                            class="rounded border-gray-300 dark:border-zinc-800 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-950" />
                        <Label for="is_active" class="cursor-pointer">Activo</Label>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="ghost" @click="dialogOpen = false">Cancelar</Button>
                        <Button type="submit"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                            :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : (editingType ? 'Actualizar' : 'Crear') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>

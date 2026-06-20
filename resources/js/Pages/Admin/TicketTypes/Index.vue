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
    ticketTypes: Object,
    companies: Array,
});

const perPage = ref(props.ticketTypes?.per_page ?? 10);
const isDark = useDark();
const permissions = computed(() => usePage().props.auth.permissions ?? []);

const canCreate = computed(() => permissions.value.includes('ticket-types.create'));
const canUpdate = computed(() => permissions.value.includes('ticket-types.update'));
const canDelete = computed(() => permissions.value.includes('ticket-types.delete'));

const dialogOpen = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    description: '',
    is_active: true,
    company_ids: [],
});

const openCreate = () => {
    editingType.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.company_ids = [];
    dialogOpen.value = true;
};

const openEdit = (ticketType) => {
    editingType.value = ticketType;
    form.reset();
    form.clearErrors();
    form.name = ticketType.name;
    form.description = ticketType.description ?? '';
    form.is_active = ticketType.is_active;
    form.company_ids = ticketType.companies?.map(c => c.id) ?? [];
    dialogOpen.value = true;
};

const submit = () => {
    if (editingType.value) {
        form.put(route('admin.ticket-types.update', editingType.value.id), {
            onSuccess: () => {
                dialogOpen.value = false;
                editingType.value = null;
            },
        });
    } else {
        form.post(route('admin.ticket-types.store'), {
            onSuccess: () => {
                dialogOpen.value = false;
            },
        });
    }
};

const changePerPage = () => {
    router.get(route('admin.ticket-types.index'), { perPage: perPage.value }, { preserveState: true, replace: true });
};

const confirmDelete = (ticketType) => {
    Swal.fire({
        title: '¿Eliminar tipo?',
        text: `Se eliminará "${ticketType.name}" permanentemente.`,
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
            router.delete(route('admin.ticket-types.destroy', ticketType.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El tipo de solicitud ha sido borrado.',
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
    <Head title="Tipos de Solicitud" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Tipos de Solicitud</span>
                <Button v-if="canCreate" variant="outline" @click="openCreate"
                    class="h-9 px-4 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-colors">
                    + Nuevo Tipo
                </Button>
            </div>
        </template>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Administrar Tipos de Solicitud</CardTitle>
                <CardDescription>Define los tipos de solicitudes disponibles para las compañías.</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nombre</TableHead>
                            <TableHead>Descripción</TableHead>
                            <TableHead>Compañías</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="ticketTypes.data.length === 0">
                            <TableCell colspan="5" class="text-center text-muted-foreground h-24">
                                No hay tipos de solicitud registrados aún.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="type in ticketTypes.data" :key="type.id" v-else>
                            <TableCell class="font-medium text-foreground">{{ type.name }}</TableCell>
                            <TableCell class="text-muted-foreground max-w-xs truncate">{{ type.description || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground max-w-xs">
                                <template v-if="type.companies?.length">
                                    <span v-for="(c, i) in type.companies" :key="c.id">
                                        {{ c.name }}<template v-if="i < type.companies.length - 1">, </template>
                                    </span>
                                </template>
                                <span v-else class="text-zinc-400 dark:text-zinc-600">—</span>
                            </TableCell>
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

                <div v-if="ticketTypes.total" class="flex items-center justify-between mt-6">
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
                        Mostrando <span class="font-medium">{{ ticketTypes.from }}</span> a <span class="font-medium">{{ ticketTypes.to }}</span> de <span class="font-medium">{{ ticketTypes.total }}</span> registros
                    </p>
                    <div v-if="ticketTypes.links?.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, i) in ticketTypes.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors"
                                :class="link.active ? 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-zinc-950 font-semibold' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                                <span v-html="link.label" />
                            </Link>
                            <span v-else class="px-3 py-1.5 text-sm rounded-md text-gray-400 dark:text-zinc-600"
                                :class="{ 'cursor-not-allowed': i === 0 || i === ticketTypes.links.length - 1 }"
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
                        {{ editingType ? 'Modifica los datos del tipo de solicitud.' : 'Ingresa los datos del nuevo tipo de solicitud.' }}
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
                        <textarea id="description" v-model="form.description" rows="3"
                            class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 placeholder-gray-400 dark:placeholder-zinc-500 resize-y"></textarea>
                        <p class="text-sm text-red-500" v-if="form.errors.description">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" type="checkbox" v-model="form.is_active"
                            class="rounded border-gray-300 dark:border-zinc-800 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-950" />
                        <Label for="is_active" class="cursor-pointer">Activo</Label>
                    </div>

                    <div class="space-y-2">
                        <Label>Compañías asociadas</Label>
                        <div class="max-h-40 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-zinc-800 rounded-md p-3">
                            <div v-for="company in companies" :key="company.id" class="flex items-center gap-2">
                                <input :id="'company_' + company.id" type="checkbox" :value="company.id" v-model="form.company_ids"
                                    class="rounded border-gray-300 dark:border-zinc-800 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-950" />
                                <Label :for="'company_' + company.id" class="cursor-pointer text-sm font-normal">{{ company.name }}</Label>
                            </div>
                            <p v-if="companies.length === 0" class="text-sm text-muted-foreground">No hay compañías activas.</p>
                        </div>
                        <p class="text-sm text-red-500" v-if="form.errors.company_ids">{{ form.errors.company_ids }}</p>
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

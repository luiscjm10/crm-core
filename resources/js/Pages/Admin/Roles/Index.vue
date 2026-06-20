<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed } from 'vue';
import { useDark } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps({
    roles: Object,
    permissions: Array,
});

const perPage = ref(props.roles?.per_page ?? 10);

const changePerPage = () => {
    router.get(route('admin.roles.index'), { perPage: perPage.value }, { preserveState: true, replace: true });
};

const isDark = useDark();

const permissions = computed(() => usePage().props.auth.permissions ?? []);
const canCreate = computed(() => permissions.value.includes('roles.create'));
const canUpdate = computed(() => permissions.value.includes('roles.update'));
const canDelete = computed(() => permissions.value.includes('roles.delete'));

const deleteRole = (id, name) => {
    Swal.fire({
        title: '¿Eliminar rol?',
        text: `Se eliminará "${name}" permanentemente.`,
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
            router.delete(route('admin.roles.destroy', id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El rol ha sido borrado.',
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
    <Head title="Roles y Permisos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Roles y Permisos</span>
                <Button v-if="canCreate" variant="outline" as-child
                    class="h-9 px-4 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-colors">
                    <Link :href="route('admin.roles.create')">+ Nuevo Rol</Link>
                </Button>
            </div>
        </template>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Roles del Sistema</CardTitle>
                <CardDescription>Gestiona los roles y asigna permisos a cada uno.</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Rol</TableHead>
                            <TableHead>Permisos</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="roles.data.length === 0">
                            <TableCell colspan="3" class="text-center text-muted-foreground h-24">
                                No hay roles creados aún.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="role in roles.data" :key="role.id" v-else>
                            <TableCell class="font-medium text-foreground capitalize">{{ role.name }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <span v-if="!role.permissions?.length" class="text-muted-foreground text-sm">—</span>
                                    <span v-for="perm in role.permissions" :key="perm.id"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ perm.name }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right space-x-3">
                                <Link v-if="canUpdate" :href="route('admin.roles.edit', role.id)"
                                    class="text-primary hover:text-primary/80 font-medium transition-colors text-sm">
                                    Editar
                                </Link>
                                <button v-if="canDelete" @click="deleteRole(role.id, role.name)"
                                    class="text-destructive hover:text-destructive/80 font-medium transition-colors text-sm">
                                    Eliminar
                                </button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="roles.total" class="flex items-center justify-between mt-6">
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
                        Mostrando <span class="font-medium">{{ roles.from }}</span> a <span class="font-medium">{{ roles.to }}</span> de <span class="font-medium">{{ roles.total }}</span> registros
                    </p>
                    <div v-if="roles.links?.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, i) in roles.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors"
                                :class="link.active
                                    ? 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-zinc-950 font-semibold'
                                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                                <span v-html="link.label" />
                            </Link>
                            <span v-else
                                class="px-3 py-1.5 text-sm rounded-md text-gray-400 dark:text-zinc-600"
                                :class="{ 'cursor-not-allowed': i === 0 || i === roles.links.length - 1 }"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

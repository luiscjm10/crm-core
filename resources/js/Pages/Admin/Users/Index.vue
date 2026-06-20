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
    users: Object,
});

const perPage = ref(props.users?.per_page ?? 10);

const changePerPage = () => {
    router.get(route('admin.users.index'), { perPage: perPage.value }, { preserveState: true, replace: true });
};

const isDark = useDark();

const permissions = computed(() => usePage().props.auth.permissions ?? []);
const canCreate = computed(() => permissions.value.includes('users.create'));
const canUpdate = computed(() => permissions.value.includes('users.update'));
const canDelete = computed(() => permissions.value.includes('users.delete'));

const deleteUser = (id, name) => {
    Swal.fire({
        title: '¿Eliminar usuario?',
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
            router.delete(route('admin.users.destroy', id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El usuario ha sido borrado.',
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

    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Usuarios</span>
                <Button v-if="canCreate" variant="outline" as-child
                    class="h-9 px-4 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-colors">
                    <Link :href="route('admin.users.create')">+ Nuevo Usuario</Link>
                </Button>
            </div>
        </template>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Directorio de Usuarios</CardTitle>
                <CardDescription>Gestiona los usuarios del sistema y sus roles.</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-16">ID</TableHead>
                            <TableHead>Nombre</TableHead>
                            <TableHead>Apellidos</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Rol</TableHead>
                            <TableHead>Compañía</TableHead>
                            <TableHead>Compañías asignadas</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="users.data.length === 0">
                            <TableCell colspan="8" class="text-center text-muted-foreground h-24">
                                No hay usuarios registrados aún.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="user in users.data" :key="user.id" v-else>
                            <TableCell class="text-muted-foreground text-sm">{{ user.id }}</TableCell>
                            <TableCell class="font-medium text-foreground">{{ user.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ user.last_name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ user.email }}</TableCell>
                            <TableCell>
                                <span v-for="role in user.roles" :key="role.id"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    {{ role.name }}
                                </span>
                                <span v-if="!user.roles.length" class="text-muted-foreground text-sm">—</span>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ user.company?.name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground max-w-xs">
                                <template v-if="user.companies?.length">
                                    <span v-for="(c, i) in user.companies" :key="c.id">
                                        {{ c.name }}<template v-if="i < user.companies.length - 1">, </template>
                                    </span>
                                </template>
                                <span v-else class="text-zinc-400 dark:text-zinc-600">—</span>
                            </TableCell>
                            <TableCell class="text-right space-x-3">
                                <Link :href="route('admin.users.show', user.id)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors text-sm">
                                    Ver
                                </Link>
                                <Link v-if="canUpdate" :href="route('admin.users.edit', user.id)"
                                    class="text-primary hover:text-primary/80 font-medium transition-colors text-sm">
                                    Editar
                                </Link>
                                <button v-if="canDelete" @click="deleteUser(user.id, user.name)"
                                    class="text-destructive hover:text-destructive/80 font-medium transition-colors text-sm">
                                    Eliminar
                                </button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="users.total" class="flex items-center justify-between mt-6">
                    <div class="flex items-center gap-2">
                        <label for="perPage" class="text-sm text-gray-500 dark:text-zinc-400">Registros por
                            página:</label>
                        <select id="perPage" v-model="perPage" @change="changePerPage"
                            class="text-sm border border-gray-300 dark:border-zinc-700 rounded-md bg-white dark:bg-zinc-900 text-gray-700 dark:text-zinc-300 px-2 pr-7 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">
                        Mostrando <span class="font-medium">{{ users.from }}</span> a <span class="font-medium">{{
                            users.to
                            }}</span> de <span class="font-medium">{{ users.total }}</span> registros
                    </p>
                    <div v-if="users.links?.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, i) in users.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors" :class="link.active
                                    ? 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-zinc-950 font-semibold'
                                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                                <span v-html="link.label" />
                            </Link>
                            <span v-else class="px-3 py-1.5 text-sm rounded-md text-gray-400 dark:text-zinc-600"
                                :class="{ 'cursor-not-allowed': i === 0 || i === users.links.length - 1 }"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

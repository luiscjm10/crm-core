<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import { useDark } from '@vueuse/core';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps({
    companies: Object,
});

const perPage = ref(props.companies?.per_page ?? 10);

const changePerPage = () => {
    router.get(route('companies.index'), { perPage: perPage.value }, { preserveState: true, replace: true });
};

const isDark = useDark();

const deleteCompany = (id) => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará la compañía permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        // Cambiamos los colores dependiendo de isDark.value
        cancelButtonColor: isDark.value ? '#3f3f46' : '#e5e7eb',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: isDark.value ? '#09090b' : '#ffffff',
        color: isDark.value ? '#fafafa' : '#111827',
        customClass: {
            // Bordes oscuros en dark mode, sombra y bordes claros en light mode
            popup: isDark.value ? 'border border-zinc-800 rounded-xl' : 'border border-gray-200 shadow-xl rounded-xl',
            cancelButton: isDark.value ? '' : 'text-gray-900' // Para que el texto del botón cancelar se vea bien en modo claro
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('companies.destroy', id), {
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'La compañía ha sido borrada.',
                        icon: 'success',
                        background: isDark.value ? '#09090b' : '#ffffff',
                        color: isDark.value ? '#fafafa' : '#111827',
                        confirmButtonColor: '#10b981', // Verde esmeralda para el éxito
                    });
                }
            });
        }
    });
};
</script>

<template>

    <Head title="Compañías" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Compañías</span>
                <Button variant="outline" as-child
                    class="h-9 px-4 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-colors">
                    <Link :href="route('companies.create')">
                        + Nueva Compañía
                    </Link>
                </Button>
            </div>
        </template>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Directorio de Empresas</CardTitle>
                <CardDescription>
                    Gestiona los clientes corporativos de tu agencia y sus representantes.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Razón Social</TableHead>
                            <TableHead>NIT/RUT</TableHead>
                            <TableHead>Rep. Legal</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="companies.data.length === 0">
                            <TableCell colspan="4" class="text-center text-muted-foreground h-24">
                                No hay compañías registradas aún.
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="company in companies.data" :key="company.id" v-else>
                            <TableCell class="font-medium text-foreground">{{ company.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ company.tax_id || 'N/A' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ company.legal_representative || 'N/A' }}
                            </TableCell>
                            <TableCell class="text-right space-x-3">

                                <Link :href="route('companies.show', company.id)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors text-sm">
                                    Ver
                                </Link>

                                <Link :href="route('companies.edit', company.id)"
                                    class="text-primary hover:text-primary/80 font-medium transition-colors text-sm">
                                    Editar
                                </Link>
                                <button @click="deleteCompany(company.id)"
                                    class="text-destructive hover:text-destructive/80 font-medium transition-colors text-sm">
                                    Eliminar
                                </button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="companies.total" class="flex items-center justify-between mt-6">
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
                        Mostrando <span class="font-medium">{{ companies.from }}</span> a <span class="font-medium">{{
                            companies.to }}</span> de <span class="font-medium">{{ companies.total }}</span> registros
                    </p>
                    <div v-if="companies.links?.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, i) in companies.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors" :class="link.active
                                    ? 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-zinc-950 font-semibold'
                                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                                <span v-html="link.label" />
                            </Link>
                            <span v-else class="px-3 py-1.5 text-sm rounded-md text-gray-400 dark:text-zinc-600"
                                :class="{ 'cursor-not-allowed': i === 0 || i === companies.links.length - 1 }"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
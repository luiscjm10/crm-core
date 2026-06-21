<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed } from 'vue';
import { useDark } from '@vueuse/core';
import { formatDateOnly, formatDateTime, formatMinutes } from '@/helpers/date';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps({
    tickets: Object,
    sort: { type: String, default: 'requested_at' },
    direction: { type: String, default: 'desc' },
});

const perPage = ref(props.tickets?.per_page ?? 10);
const isDark = useDark();

const canCreateTicket = computed(() =>
    usePage().props.auth.permissions?.includes('tickets.create')
);
const canDeleteTicket = computed(() =>
    usePage().props.auth.permissions?.includes('tickets.delete')
);
const canViewResolutionTime = computed(() =>
    usePage().props.auth.permissions?.includes('tickets.view-resolution-time')
);

const statusLabels = {
    open: 'Abierto',
    in_progress: 'En progreso',
    resolved: 'Resuelto',
    closed: 'Cerrado',
};

const statusColors = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    in_progress: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    resolved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    closed: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
};

const changePerPage = () => {
    router.get(route('admin.tickets.index'), { ...route().params, perPage: perPage.value }, { preserveState: true, replace: true });
};

const sortBy = (column) => {
    const newDirection = props.sort === column && props.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('admin.tickets.index'), {
        ...route().params,
        sort: column,
        direction: newDirection,
    }, { preserveState: true, replace: true });
};

const sortArrow = (column) => {
    if (props.sort !== column) return '';
    return props.direction === 'asc' ? ' \u25B2' : ' \u25BC';
};

const deleteTicket = (ticket) => {
    Swal.fire({
        title: '¿Eliminar solicitud?',
        text: `Se eliminará la solicitud "${ticket.uuid}" permanentemente.`,
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
            router.delete(route('admin.tickets.destroy', ticket.uuid), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Eliminada',
                        text: 'La solicitud ha sido borrada.',
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

    <Head title="Solicitudes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Solicitudes</span>
                <Button v-if="canCreateTicket" variant="outline" as-child
                    class="h-9 px-4 border-emerald-600 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:border-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-colors">
                    <Link :href="route('admin.tickets.create')">+ Nueva Solicitud</Link>
                </Button>
            </div>
        </template>

        <Card class="mt-6">
            <CardHeader>
                <CardTitle>Centro de Solicitudes</CardTitle>
                <CardDescription>Gestiona las solicitudes de soporte del sistema.</CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-24">UUID</TableHead>
                            <TableHead>Compañía</TableHead>
                            <TableHead class="cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" @click="sortBy('ticket_type')">
                                Tipo<span class="text-xs">{{ sortArrow('ticket_type') }}</span>
                            </TableHead>
                            <TableHead class="cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" @click="sortBy('status')">
                                Estado<span class="text-xs">{{ sortArrow('status') }}</span>
                            </TableHead>
                            <TableHead>Solicitante</TableHead>
                            <TableHead>Asignado</TableHead>
                            <TableHead class="cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" @click="sortBy('requested_at')">
                                Fecha Solicitud<span class="text-xs">{{ sortArrow('requested_at') }}</span>
                            </TableHead>
                            <TableHead class="cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" @click="sortBy('updated_at')">
                                Actualización<span class="text-xs">{{ sortArrow('updated_at') }}</span>
                            </TableHead>
                            <TableHead class="cursor-pointer select-none hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors" @click="sortBy('created_at')">
                                Creado<span class="text-xs">{{ sortArrow('created_at') }}</span>
                            </TableHead>
                            <TableHead v-if="canViewResolutionTime">Tiempo Resolución</TableHead>
                            <TableHead>Tiempo Invertido</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="tickets.data.length === 0">
                            <TableCell :colspan="canViewResolutionTime ? 12 : 11" class="text-center text-muted-foreground h-24">
                                No hay solicitudes registradas aún.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="ticket in tickets.data" :key="ticket.uuid" v-else>
                            <TableCell class="text-muted-foreground text-sm font-mono">{{ ticket.uuid.slice(0, 8) }}...
                            </TableCell>
                            <TableCell class="font-medium text-foreground break-words whitespace-normal">{{ ticket.company?.name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground break-words whitespace-normal">{{ ticket.ticket_type?.name || '—' }}</TableCell>
                            <TableCell>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    :class="statusColors[ticket.status] || 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300'">
                                    {{ statusLabels[ticket.status] || ticket.status }}
                                </span>
                            </TableCell>
                            <TableCell class="text-muted-foreground break-words whitespace-normal">{{ ticket.requester?.name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground break-words whitespace-normal">{{ ticket.assignee?.name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground text-sm">{{ formatDateOnly(ticket.requested_at) }}</TableCell>
                            <TableCell class="text-muted-foreground text-sm">{{ formatDateTime(ticket.updated_at) }}</TableCell>
                            <TableCell class="text-muted-foreground text-sm">{{ formatDateTime(ticket.created_at) }}</TableCell>
                            <TableCell v-if="canViewResolutionTime" class="text-muted-foreground text-sm">{{ ticket.resolution_time_human || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground text-sm">{{ formatMinutes(ticket.comments_sum_time_spent_minutes) }}</TableCell>
                            <TableCell class="text-right space-x-3">
                                <Link :href="route('admin.tickets.show', ticket.uuid)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors text-sm">
                                    Ver
                                </Link>
                                <button v-if="canDeleteTicket" @click="deleteTicket(ticket)"
                                    class="text-destructive hover:text-destructive/80 font-medium transition-colors text-sm">
                                    Eliminar
                                </button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <div v-if="tickets.total" class="flex items-center justify-between mt-6">
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
                        Mostrando <span class="font-medium">{{ tickets.from }}</span> a <span class="font-medium">{{
                            tickets.to
                        }}</span> de <span class="font-medium">{{ tickets.total }}</span> registros
                    </p>
                    <div v-if="tickets.links?.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, i) in tickets.links" :key="i">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors" :class="link.active
                                    ? 'bg-emerald-600 text-white dark:bg-emerald-600 dark:text-zinc-950 font-semibold'
                                    : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                                <span v-html="link.label" />
                            </Link>
                            <span v-else class="px-3 py-1.5 text-sm rounded-md text-gray-400 dark:text-zinc-600"
                                :class="{ 'cursor-not-allowed': i === 0 || i === tickets.links.length - 1 }"
                                v-html="link.label" />
                        </template>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

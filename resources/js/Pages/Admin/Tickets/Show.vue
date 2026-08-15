<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatDateOnly, formatDateTime, formatMinutes } from '@/helpers/date';
import Swal from 'sweetalert2';

const props = defineProps({
    ticket: Object,
    canClose: Boolean,
    canComment: Boolean,
    canLogTime: Boolean,
    canViewResolutionTime: Boolean,
    canAssign: Boolean,
    canTake: Boolean,
    canToggleStatus: Boolean,
    canChangeType: Boolean,
    canReopen: Boolean,
    companyTicketTypes: Array,
    users: Array,
});

const form = useForm({
    commentable_type: 'ticket',
    commentable_id: props.ticket.id,
    content: '',
    time_spent_minutes: null,
});

const assignForm = useForm({
    assigned_to: '',
});

const typeForm = useForm({
    ticket_type_id: '',
});

const dialogOpen = ref(false);
const typeDialogOpen = ref(false);
const searchQuery = ref('');
const selectedUserId = ref('');

const filteredUsers = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return props.users;

    return props.users.filter((user) => {
        const fullName = `${user.name} ${user.last_name ?? ''}`.toLowerCase();
        return fullName.includes(q);
    });
});

const getInitials = (user) => {
    const first = (user.name ?? '').charAt(0);
    const last = (user.last_name ?? '').charAt(0);
    return `${first}${last}`.toUpperCase();
};

const openAssignDialog = () => {
    searchQuery.value = '';
    selectedUserId.value = props.ticket.assignee?.id ? String(props.ticket.assignee.id) : '';
    assignForm.clearErrors();
    dialogOpen.value = true;
};

const openTypeDialog = () => {
    typeForm.ticket_type_id = props.ticket.ticket_type?.id ? String(props.ticket.ticket_type.id) : '';
    typeForm.clearErrors();
    typeDialogOpen.value = true;
};

const submitType = () => {
    if (!typeForm.ticket_type_id || typeForm.ticket_type_id === String(props.ticket.ticket_type?.id)) return;

    typeForm.patch(route('admin.tickets.type', props.ticket.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            typeDialogOpen.value = false;
        },
    });
};

const submitComment = () => {
    form.post(route('admin.comments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('content', 'time_spent_minutes'),
    });
};

const submitAssign = () => {
    if (!selectedUserId.value) return;

    assignForm.assigned_to = selectedUserId.value;

    assignForm.patch(route('admin.tickets.assign', props.ticket.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
            selectedUserId.value = '';
        },
    });
};

const releaseTicket = () => {
    dialogOpen.value = false;

    Swal.fire({
        title: '¿Liberar ticket?',
        text: 'El ticket quedará sin responsable asignado.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, liberar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        zIndex: 1100,
    }).then((result) => {
        if (result.isConfirmed) {
            assignForm.assigned_to = null;

            assignForm.patch(route('admin.tickets.assign', props.ticket.uuid), {
                preserveScroll: true,
                onSuccess: () => {
                    dialogOpen.value = false;
                    selectedUserId.value = '';
                },
            });
        }
    });
};

const takeTicket = () => {
    router.patch(route('admin.tickets.take', props.ticket.uuid));
};

const toggleStatus = () => {
    router.patch(route('admin.tickets.status', props.ticket.uuid));
};

const closeTicket = () => {
    Swal.fire({
        title: '¿Cerrar ticket?',
        text: 'Una vez cerrado no podrá agregar más comentarios.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        zIndex: 1100,
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('admin.tickets.close', props.ticket.uuid));
        }
    });
};

const reopenTicket = () => {
    Swal.fire({
        title: '¿Reabrir solicitud?',
        text: 'La solicitud volverá al estado Abierto.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        zIndex: 1100,
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('admin.tickets.reopen', props.ticket.uuid));
        }
    });
};

const statusLabels = {
    open: 'Abierto',
    in_progress: 'En progreso',
    closed: 'Cerrado',
};

const statusColors = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    in_progress: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    closed: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
};

const isTicketClosed = props.ticket.status === 'closed';

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Solicitud - ${ticket.uuid}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.tickets.index')"
                    class="text-gray-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors flex items-center justify-center h-8 w-8 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </Link>
                <span>Detalle de Solicitud</span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader
                class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6 flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl font-mono text-sm">{{ ticket.uuid }}</CardTitle>
                    <CardDescription>Información general de la solicitud.</CardDescription>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button v-if="canTake" @click="takeTicket"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-sm">
                        Tomar solicitud
                    </Button>
                    <Button v-if="canToggleStatus && ticket.status === 'open'" @click="toggleStatus"
                        class="bg-amber-600 text-white hover:bg-amber-700 dark:bg-amber-600 dark:hover:bg-amber-500 text-sm">
                        Comenzar
                    </Button>
                    <Button v-if="canToggleStatus && ticket.status === 'in_progress'" @click="toggleStatus"
                        variant="outline"
                        class="border-amber-500 text-amber-600 dark:border-amber-500/60 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 text-sm">
                        Volver a abierto
                    </Button>
                    <Button v-if="canAssign && !isTicketClosed" @click="openAssignDialog"
                        variant="outline"
                        class="border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 text-sm">
                        Asignar
                    </Button>
                    <Button v-if="canChangeType" @click="openTypeDialog"
                        variant="outline"
                        class="border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 text-sm">
                        Cambiar tipo
                    </Button>
                    <Button v-if="canClose" @click="closeTicket"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-sm">
                        Cerrar Ticket
                    </Button>
                    <Button v-if="isTicketClosed && canReopen" @click="reopenTicket"
                        variant="outline"
                        class="border-emerald-500 text-emerald-600 dark:border-emerald-500/60 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-sm">
                        Reabrir
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">UUID</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-mono text-sm">{{ ticket.uuid }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Estado</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                            :class="statusColors[ticket.status] || 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300'">
                            {{ statusLabels[ticket.status] || ticket.status }}
                        </span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Compañía</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ ticket.company?.name || '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Tipo de Solicitud</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ ticket.ticket_type?.name || '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Solicitante</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ ticket.requester?.name || '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Asignado a</p>
                        <div v-if="ticket.assignee" class="flex items-center gap-2.5">
                            <span
                                class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-semibold shrink-0">
                                {{ getInitials(ticket.assignee) }}
                            </span>
                            <span class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                                {{ ticket.assignee.name }} {{ ticket.assignee.last_name ?? '' }}
                            </span>
                        </div>
                        <div v-else class="flex items-center gap-2.5">
                            <span
                                class="inline-flex items-center justify-center h-8 w-8 rounded-full border border-dashed border-gray-300 dark:border-zinc-700 text-gray-400 dark:text-zinc-600 shrink-0">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <span class="text-base text-gray-500 dark:text-zinc-500 font-medium">Sin asignar</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Fecha de Solicitud</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatDateTime(ticket.requested_at) }}</p>
                    </div>
                    <div v-if="canViewResolutionTime && ticket.resolution_time_human" class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Tiempo de resolución</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ ticket.resolution_time_human }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Tiempo Total Invertido</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatMinutes(ticket.total_time_spent_minutes) }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Creado por</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ ticket.creator?.name || '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Creado el</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatDate(ticket.created_at) }}</p>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Descripción</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 whitespace-pre-wrap">{{ ticket.description }}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100 mb-6">Comentarios</h3>

            <div class="relative">
                <div class="absolute left-[19px] top-0 bottom-0 w-px bg-zinc-200 dark:bg-zinc-700"></div>

                <div class="space-y-6">
                    <div v-for="comment in ticket.comments" :key="comment.id" class="relative pl-10">
                        <div class="absolute left-[13px] top-1.5 w-3 h-3 rounded-full border-2 bg-white dark:bg-zinc-900 z-10 ring-2 ring-white dark:ring-zinc-950"
                            :class="comment.is_system ? 'border-zinc-300 dark:border-zinc-600' : 'border-emerald-500'">
                        </div>
                        <div v-if="comment.is_system"
                            class="text-xs text-zinc-400 dark:text-zinc-500 text-center py-2 select-none">
                            {{ comment.content }}
                        </div>
                        <div v-else
                            class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ comment.user?.name || 'Usuario' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span v-if="comment.time_spent_minutes > 0"
                                        class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 rounded-full">
                                        ⏱️ {{ formatMinutes(comment.time_spent_minutes) }}
                                    </span>
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ formatDate(comment.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ comment.content }}</p>
                        </div>
                    </div>

                    <div v-if="!isTicketClosed && canComment" class="relative pl-10">
                        <div class="absolute left-[13px] top-1.5 w-3 h-3 rounded-full border-2 border-dashed border-emerald-400 dark:border-emerald-600 bg-white dark:bg-zinc-900 z-10 ring-2 ring-white dark:ring-zinc-950">
                        </div>
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/80 p-4 shadow-sm">
                            <form @submit.prevent="submitComment">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2" for="comment-content">
                                    Agregar comentario
                                </label>
                                <textarea id="comment-content" v-model="form.content" spellcheck="true" lang="es-ES"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:border-transparent resize-none"
                                    rows="3" placeholder="Escribe tu comentario..." :disabled="form.processing"></textarea>
                                <div v-if="form.errors.content" class="mt-1 text-sm text-red-500">{{ form.errors.content }}</div>
                                <div v-if="canLogTime" class="mt-3">
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1" for="time_spent_minutes">
                                        Tiempo invertido (en minutos)
                                    </label>
                                    <input id="time_spent_minutes" type="number" v-model="form.time_spent_minutes" min="1"
                                        class="flex w-40 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400"
                                        placeholder="0" :disabled="form.processing" />
                                    <div v-if="form.errors.time_spent_minutes" class="mt-1 text-sm text-red-500">{{ form.errors.time_spent_minutes }}</div>
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <Button type="submit" :disabled="form.processing || !form.content.trim()"
                                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-sm">
                                        Enviar
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="!ticket.comments?.length && isTicketClosed" class="text-sm text-zinc-400 dark:text-zinc-500 italic">
                No hay comentarios.
            </p>
            <p v-if="!ticket.comments?.length && !isTicketClosed && !canComment" class="text-sm text-zinc-400 dark:text-zinc-500 italic">
                No hay comentarios aún.
            </p>
            <p v-if="!ticket.comments?.length && !isTicketClosed && canComment" class="text-sm text-zinc-400 dark:text-zinc-500 italic mt-4">
                No hay comentarios aún. Sé el primero en responder.
            </p>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Asignar solicitud</DialogTitle>
                    <DialogDescription>
                        Ticket <span class="font-mono">{{ ticket.uuid }}</span> — selecciona el responsable.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <input v-model="searchQuery" type="search" placeholder="Buscar usuario..."
                        class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 placeholder-gray-400 dark:placeholder-zinc-500" />

                    <div class="max-h-72 overflow-y-auto space-y-1 pr-1">
                        <button v-for="user in filteredUsers" :key="user.id" type="button" @click="selectedUserId = String(user.id)"
                            class="w-full flex items-center gap-3 rounded-lg border px-3 py-2.5 text-left transition-colors"
                            :class="selectedUserId === String(user.id)
                                ? 'border-emerald-500 bg-emerald-50 dark:border-emerald-500 dark:bg-emerald-950/40'
                                : 'border-transparent hover:bg-gray-100 dark:hover:bg-zinc-800/60'">
                            <span
                                class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 text-xs font-semibold shrink-0">
                                {{ getInitials(user) }}
                            </span>
                            <span class="flex-1 text-sm font-medium text-gray-900 dark:text-zinc-100">
                                {{ user.name }} {{ user.last_name ?? '' }}
                            </span>
                            <span v-if="selectedUserId === String(user.id)"
                                class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-emerald-600 text-white shrink-0">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                        </button>

                        <p v-if="filteredUsers.length === 0" class="text-sm text-gray-400 dark:text-zinc-500 text-center py-6">
                            No se encontraron usuarios.
                        </p>
                    </div>

                    <p v-if="assignForm.errors.assigned_to" class="text-sm text-red-500">{{ assignForm.errors.assigned_to }}</p>
                </div>

                <DialogFooter class="pt-2">
                    <Button v-if="ticket.assignee" type="button" variant="ghost" @click="releaseTicket"
                        class="mr-auto text-gray-500 dark:text-zinc-400 hover:text-red-500 dark:hover:text-red-400">
                        Liberar
                    </Button>
                    <Button type="button" variant="ghost" @click="dialogOpen = false">Cancelar</Button>
                    <Button type="button" @click="submitAssign"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                        :disabled="!selectedUserId || assignForm.processing">
                        {{ assignForm.processing ? 'Asignando...' : 'Asignar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="typeDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Cambiar tipo de solicitud</DialogTitle>
                    <DialogDescription>
                        Ticket <span class="font-mono">{{ ticket.uuid }}</span> — selecciona el nuevo tipo.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="space-y-2">
                        <Label for="ticket_type_id">Tipo actual: <span class="font-medium text-gray-900 dark:text-zinc-100">{{ ticket.ticket_type?.name || '—' }}</span></Label>
                        <select id="ticket_type_id" v-model="typeForm.ticket_type_id"
                            class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                            <option value="">Seleccionar tipo...</option>
                            <option v-for="type in companyTicketTypes" :key="type.id" :value="type.id" :disabled="String(type.id) === String(ticket.ticket_type?.id)">
                                {{ type.name }}
                            </option>
                        </select>
                        <p v-if="typeForm.errors.ticket_type_id" class="text-sm text-red-500">{{ typeForm.errors.ticket_type_id }}</p>
                    </div>
                </div>

                <DialogFooter class="pt-2">
                    <Button type="button" variant="ghost" @click="typeDialogOpen = false">Cancelar</Button>
                    <Button type="button" @click="submitType"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                        :disabled="!typeForm.ticket_type_id || typeForm.ticket_type_id === String(ticket.ticket_type?.id) || typeForm.processing">
                        {{ typeForm.processing ? 'Guardando...' : 'Cambiar tipo' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>

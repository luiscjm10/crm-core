<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { formatDateOnly } from '@/helpers/date';
import Swal from 'sweetalert2';

const props = defineProps({
    ticket: Object,
    canClose: Boolean,
    canComment: Boolean,
});

const form = useForm({
    commentable_type: 'ticket',
    commentable_id: props.ticket.id,
    content: '',
});

const submitComment = () => {
    form.post(route('admin.comments.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('content'),
    });
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
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('admin.tickets.close', props.ticket.uuid));
        }
    });
};

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
                <div class="flex items-center gap-2">
                    <Button v-if="canClose" @click="closeTicket"
                        class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-sm">
                        Cerrar Ticket
                    </Button>
                    <Button variant="outline" as-child
                        class="border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800">
                        <Link :href="route('admin.tickets.index')">Volver al listado</Link>
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
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ ticket.assignee?.name || 'Sin asignar' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Fecha de Solicitud</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ formatDateOnly(ticket.requested_at) }}</p>
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

        <div class="mt-8 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-zinc-100">Comentarios</h3>

            <div v-if="ticket.comments?.length" class="space-y-4">
                <div v-for="comment in ticket.comments" :key="comment.id">
                    <div v-if="comment.is_system"
                        class="text-center text-xs text-gray-400 dark:text-zinc-500 py-3">
                        {{ comment.content }}
                    </div>
                    <div v-else
                        class="bg-white dark:bg-zinc-900/50 border border-gray-200 dark:border-zinc-800 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-zinc-100">
                                {{ comment.user?.name || 'Usuario' }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-zinc-500">
                                {{ formatDate(comment.created_at) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-zinc-300 whitespace-pre-wrap">{{ comment.content }}</p>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-400 dark:text-zinc-500 italic">
                No hay comentarios aún.
            </p>

            <div v-if="!isTicketClosed && canComment"
                class="bg-white dark:bg-zinc-900/50 border border-gray-200 dark:border-zinc-800 rounded-lg p-4 shadow-sm">
                <form @submit.prevent="submitComment">
                    <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2" for="comment-content">
                        Agregar comentario
                    </label>
                    <textarea id="comment-content" v-model="form.content"
                        class="w-full rounded-lg border border-gray-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 focus:border-transparent resize-none"
                        rows="3" placeholder="Escribe tu comentario..." :disabled="form.processing"></textarea>
                    <div v-if="form.errors.content" class="mt-1 text-sm text-red-500">{{ form.errors.content }}</div>
                    <div class="mt-3 flex justify-end">
                        <Button type="submit" :disabled="form.processing || !form.content.trim()"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-sm">
                            Enviar
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

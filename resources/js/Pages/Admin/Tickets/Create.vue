<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';

const props = defineProps({
    companies: Array,
});

const form = useForm({
    company_id: '',
    ticket_type_id: '',
    description: '',
});

const ticketTypes = ref([]);
const loadingTypes = ref(false);

watch(() => form.company_id, async (companyId) => {
    form.ticket_type_id = '';
    ticketTypes.value = [];

    if (!companyId) return;

    loadingTypes.value = true;

    try {
        const response = await fetch(`/api/companies/${companyId}/ticket-types`);
        ticketTypes.value = await response.json();
    } finally {
        loadingTypes.value = false;
    }
});

const submit = () => {
    form.post(route('admin.tickets.store'));
};
</script>

<template>
    <Head title="Nueva Solicitud" />

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
                <span>Nueva Solicitud</span>
            </div>
        </template>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <Card class="border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
                    <CardHeader class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6">
                        <CardTitle>Registrar Solicitud</CardTitle>
                        <CardDescription>Completa los datos para generar una nueva solicitud de soporte.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="company_id">Compañía <span class="text-red-500">*</span></Label>
                                    <select id="company_id" v-model="form.company_id" required
                                        class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                        <option value="">Seleccionar compañía...</option>
                                        <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                                    </select>
                                    <p class="text-sm text-red-500" v-if="form.errors.company_id">{{ form.errors.company_id }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="ticket_type_id">Tipo de Solicitud <span class="text-red-500">*</span></Label>
                                    <select id="ticket_type_id" v-model="form.ticket_type_id" required
                                        :disabled="!form.company_id || loadingTypes"
                                        class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <option value="">
                                            {{ loadingTypes ? 'Cargando...' : 'Seleccionar tipo...' }}
                                        </option>
                                        <option v-for="type in ticketTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                    </select>
                                    <p class="text-sm text-red-500" v-if="form.errors.ticket_type_id">{{ form.errors.ticket_type_id }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Descripción <span class="text-red-500">*</span></Label>
                                <textarea id="description" v-model="form.description" rows="6" required
                                    class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 resize-y"></textarea>
                                <p class="text-sm text-red-500" v-if="form.errors.description">{{ form.errors.description }}</p>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                                <Button type="button" variant="ghost" as-child>
                                    <Link :href="route('admin.tickets.index')">Cancelar</Link>
                                </Button>
                                <Button type="submit"
                                    class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                                    :disabled="form.processing">
                                    {{ form.processing ? 'Guardando...' : 'Crear Solicitud' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <div class="lg:col-span-1">
                <Card class="border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
                    <CardHeader class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6">
                        <CardTitle class="text-base">Resumen</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-zinc-500 font-medium">Solicitante</p>
                            <p class="text-gray-900 dark:text-zinc-100">{{ usePage().props.auth.user.name }} {{ usePage().props.auth.user.last_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-zinc-500 font-medium">Estado inicial</p>
                            <p class="text-gray-900 dark:text-zinc-100">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    Abierto
                                </span>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

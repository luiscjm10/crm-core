<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

defineProps({
    company: Object,
});

const canUpdate = computed(() =>
    usePage().props.auth.permissions?.includes('companies.update')
);
</script>

<template>

    <Head :title="`Detalles - ${company.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('companies.index')"
                    class="text-gray-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors flex items-center justify-center h-8 w-8 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </Link>
                <span class="text-gray-900 dark:text-zinc-100">Ficha de la Compañía</span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader
                class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6 flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl text-gray-900 dark:text-zinc-100">{{ company.name }}</CardTitle>
                    <CardDescription class="text-gray-500 dark:text-zinc-400">
                        Información general y datos de contacto registrados.
                    </CardDescription>
                </div>
                <Button v-if="canUpdate" variant="outline" as-child
                    class="border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors">
                    <Link :href="route('companies.edit', company.id)">Editar Datos</Link>
                </Button>
            </CardHeader>

            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">NIT / RUT</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{
                                company.tax_id || 'No registrado'
                            }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Correo Electrónico Corporativo
                        </p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{
                                company.email || 'No registrado'
                            }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Representante Legal</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{
                                company.legal_representative || 'No registrado'
                            }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Teléfono del Representante</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{
                                company.legal_representative_phone
                                || 'No registrado' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Teléfono General</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{ company.phone || 'No registrado' }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Sitio Web</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            <a v-if="company.website" :href="company.website" target="_blank"
                                class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ company.website }}
                            </a>
                            <span v-else>No registrado</span>
                        </p>
                    </div>

                    <div class="space-y-1 md:col-span-2 pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Dirección Física</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{ company.address || 'No registrado'
                            }}</p>
                    </div>

                </div>

                <div v-if="company.description" class="mt-8 pt-6 border-t border-gray-100 dark:border-zinc-800/50">
                    <p class="text-sm font-medium text-gray-500 dark:text-zinc-500 mb-2">Descripción</p>
                    <p class="text-base text-gray-900 dark:text-zinc-100 leading-relaxed">{{ company.description }}</p>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
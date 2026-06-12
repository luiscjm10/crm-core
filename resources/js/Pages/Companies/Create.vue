<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = useForm({
    name: '',
    tax_id: '',
    legal_representative: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    description: '',
});

const submit = () => {
    form.post(route('companies.store'));
};
</script>

<template>

    <Head title="Nueva Compañía" />

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
                <span class="text-gray-900 dark:text-zinc-100">Registrar Nueva Compañía</span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6">
                <CardTitle class="text-xl text-gray-900 dark:text-zinc-100">Datos de la Empresa</CardTitle>
                <CardDescription class="text-gray-500 dark:text-zinc-400">
                    Ingresa la información legal y de contacto del nuevo cliente corporativo.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <Label for="name" class="text-gray-700 dark:text-zinc-300">Razón Social <span
                                    class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100"
                                required />
                            <p class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="tax_id" class="text-gray-700 dark:text-zinc-300">NIT / RUT <span
                                    class="text-red-500">*</span></Label>
                            <Input id="tax_id" v-model="form.tax_id" type="text"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100"
                                required />
                            <p class="text-sm text-red-500" v-if="form.errors.tax_id">{{ form.errors.tax_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="legal_representative" class="text-gray-700 dark:text-zinc-300">Representante
                                Legal</Label>
                            <Input id="legal_representative" v-model="form.legal_representative" type="text"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100" />
                        </div>

                        <div class="space-y-2">
                            <Label for="email" class="text-gray-700 dark:text-zinc-300">Correo Electrónico</Label>
                            <Input id="email" v-model="form.email" type="email"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100" />
                        </div>

                        <div class="space-y-2">
                            <Label for="phone" class="text-gray-700 dark:text-zinc-300">Teléfono General</Label>
                            <Input id="phone" v-model="form.phone" type="text"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100" />
                        </div>

                        <div class="space-y-2">
                            <Label for="address" class="text-gray-700 dark:text-zinc-300">Dirección Física</Label>
                            <Input id="address" v-model="form.address" type="text"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100" />
                        </div>

                        <div class="space-y-2">
                            <Label for="website" class="text-gray-700 dark:text-zinc-300">Sitio Web</Label>
                            <Input id="website" v-model="form.website" type="url" placeholder="https://ejemplo.com"
                                class="bg-white dark:bg-zinc-950 border-gray-300 dark:border-zinc-800 focus-visible:ring-emerald-500 text-gray-900 dark:text-zinc-100" />
                            <p class="text-sm text-red-500" v-if="form.errors.website">{{ form.errors.website }}</p>
                        </div>

                    </div>

                    <div class="space-y-2">
                        <Label for="description" class="text-gray-700 dark:text-zinc-300">Descripción</Label>
                        <textarea id="description" v-model="form.description"
                            class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 placeholder:text-gray-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-50 min-h-[100px]"
                            placeholder="Breve descripción de la empresa..."></textarea>
                        <p class="text-sm text-red-500" v-if="form.errors.description">{{ form.errors.description }}</p>
                    </div>

                    <div
                        class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                        <Button type="button" variant="ghost"
                            class="text-gray-600 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                            as-child>
                            <Link :href="route('companies.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 transition-colors"
                            :disabled="form.processing">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Guardar Compañía</span>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
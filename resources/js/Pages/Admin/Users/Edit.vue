<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    user: Object,
    roles: Array,
    companies: Array,
});

const form = useForm({
    name: props.user.name || '',
    last_name: props.user.last_name || '',
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
    role: props.user.roles?.[0]?.name || '',
    company_id: props.user.company_id?.toString() || '',
    company_ids: props.user.companies?.map(c => c.id) ?? [],
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head title="Editar Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')"
                    class="text-gray-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors flex items-center justify-center h-8 w-8 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </Link>
                <span>Editar Usuario: <span class="text-gray-500 dark:text-zinc-400 font-normal">{{ props.user.name }}</span></span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6">
                <CardTitle>Actualizar Datos</CardTitle>
                <CardDescription>Modifica la información del usuario y su rol.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="name">Nombre <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" type="text" required />
                            <p class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="last_name">Apellidos</Label>
                            <Input id="last_name" v-model="form.last_name" type="text" />
                            <p class="text-sm text-red-500" v-if="form.errors.last_name">{{ form.errors.last_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email <span class="text-red-500">*</span></Label>
                            <Input id="email" v-model="form.email" type="email" required />
                            <p class="text-sm text-red-500" v-if="form.errors.email">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">Nueva Contraseña <span class="text-gray-400">(opcional)</span></Label>
                            <Input id="password" v-model="form.password" type="password" />
                            <p class="text-sm text-red-500" v-if="form.errors.password">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation">Confirmar Contraseña</Label>
                            <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Rol</Label>
                            <select id="role" v-model="form.role"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <option value="">Seleccionar rol...</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <Label for="company_id">Compañía principal</Label>
                            <select id="company_id" v-model="form.company_id"
                                class="flex w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <option value="">Seleccionar compañía...</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Compañías asignadas</Label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 border border-gray-200 dark:border-zinc-800 rounded-md p-3 max-h-48 overflow-y-auto">
                            <div v-for="company in companies" :key="company.id" class="flex items-center gap-2">
                                <input :id="'edit_company_' + company.id" type="checkbox" :value="company.id" v-model="form.company_ids"
                                    class="rounded border-gray-300 dark:border-zinc-800 text-emerald-600 focus:ring-emerald-500 dark:bg-zinc-950" />
                                <Label :for="'edit_company_' + company.id" class="cursor-pointer text-sm font-normal">{{ company.name }}</Label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                        <Button type="button" variant="ghost" as-child>
                            <Link :href="route('admin.users.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                            :disabled="form.processing">
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Usuario' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

defineProps({
    user: Object,
});
</script>

<template>
    <Head :title="`Usuario - ${user.name}`" />

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
                <span>Ficha del Usuario</span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader
                class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6 flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-xl">{{ user.name }}</CardTitle>
                    <CardDescription>Información general del usuario.</CardDescription>
                </div>
                <Button variant="outline" as-child
                    class="border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800">
                    <Link :href="route('admin.users.edit', user.id)">Editar Datos</Link>
                </Button>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Nombre</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ user.name }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Email</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">{{ user.email }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Rol</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            <span v-if="user.roles?.length" v-for="role in user.roles" :key="role.id"
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                {{ role.name }}
                            </span>
                            <span v-else class="text-muted-foreground">Sin rol asignado</span>
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-zinc-500">Compañía</p>
                        <p class="text-base text-gray-900 dark:text-zinc-100 font-medium">
                            {{ user.company?.name || 'No asignada' }}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

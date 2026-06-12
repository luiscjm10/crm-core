<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps({
    role: Object,
    permissions: Array,
});

const initialPermIds = props.role.permissions?.map(p => p.id) || [];

const form = useForm({
    name: props.role.name || '',
    permissions: [...initialPermIds],
});

const togglePermission = (id) => {
    const idx = form.permissions.indexOf(id);
    if (idx === -1) {
        form.permissions.push(id);
    } else {
        form.permissions.splice(idx, 1);
    }
};

const groupedPermissions = computed(() => {
    const groups = {};
    for (const perm of props.permissions) {
        const [module] = perm.name.split('.');
        if (!groups[module]) {
            groups[module] = [];
        }
        groups[module].push(perm);
    }
    const sorted = Object.entries(groups).map(([module, perms]) => ({
        module,
        perms: perms.sort((a, b) => (a.group ?? 0) - (b.group ?? 0)),
        order: Math.min(...perms.map(p => p.group ?? 999)),
    }));
    sorted.sort((a, b) => a.order - b.order);
    return sorted;
});

const submit = () => {
    form.put(route('admin.roles.update', props.role.id));
};
</script>

<template>
    <Head title="Editar Rol" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.roles.index')"
                    class="text-gray-500 hover:text-emerald-600 dark:text-zinc-400 dark:hover:text-emerald-400 transition-colors flex items-center justify-center h-8 w-8 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </Link>
                <span>Editar Rol: <span class="text-gray-500 dark:text-zinc-400 font-normal">{{ props.role.name }}</span></span>
            </div>
        </template>

        <Card class="mt-6 border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 shadow-sm dark:shadow-none">
            <CardHeader class="border-b border-gray-100 dark:border-zinc-800/50 pb-4 mb-6">
                <CardTitle>Actualizar Rol</CardTitle>
                <CardDescription>Modifica el nombre y los permisos del rol.</CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="space-y-2 max-w-md">
                        <Label for="name">Nombre del Rol <span class="text-red-500">*</span></Label>
                        <Input id="name" v-model="form.name" type="text" required />
                        <p class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                        <Label class="text-base font-medium">Permisos</Label>
                        <p class="text-sm text-gray-500 dark:text-zinc-400 mb-4">Selecciona los permisos para este rol.</p>

                        <div v-for="group in groupedPermissions" :key="group.module" class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wider mb-2">{{ group.module }}</h4>
                            <div class="flex flex-wrap gap-3">
                                <label v-for="perm in group.perms" :key="perm.id"
                                    class="flex flex-col gap-1 p-3 rounded-md border border-gray-200 dark:border-zinc-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors min-w-[180px]">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :value="perm.id" :checked="form.permissions.includes(perm.id)"
                                            @change="togglePermission(perm.id)"
                                            class="rounded border-gray-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500" />
                                        <span class="text-sm text-gray-700 dark:text-zinc-300 font-medium">{{ perm.name }}</span>
                                    </div>
                                    <span v-if="perm.description" class="text-xs text-gray-400 dark:text-zinc-500 ml-6">{{ perm.description }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100 dark:border-zinc-800/50">
                        <Button type="button" variant="ghost" as-child>
                            <Link :href="route('admin.roles.index')">Cancelar</Link>
                        </Button>
                        <Button type="submit"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                            :disabled="form.processing">
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Rol' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>

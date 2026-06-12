<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

// 1. Importamos la lógica de VueUse y los iconos de Lucide
import { useDark, useToggle } from '@vueuse/core';
import { Moon, Sun, ChevronDown } from 'lucide-vue-next';

const isDark = useDark();
const toggleDark = useToggle(isDark);

const page = usePage();
const user = page.props.auth.user;
const isSuperAdmin = user?.roles?.some(r => r.name === 'super-admin');
const isOnAdminRoute = computed(() => route().current('admin.users.*') || route().current('admin.roles.*'));
const isAdminOpen = ref(isOnAdminRoute.value);
</script>

<template>
    <div
        class="flex h-screen bg-gray-50 dark:bg-zinc-950 text-gray-900 dark:text-zinc-50 font-sans antialiased transition-colors duration-300">

        <aside
            class="w-64 flex-shrink-0 bg-white dark:bg-zinc-900 border-r border-gray-200 dark:border-zinc-800 flex flex-col transition-colors duration-300">
            <div
                class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-950/50">
                <Link :href="route('dashboard')" class="flex items-center gap-3 text-lg font-bold tracking-tight">
                    <div
                        class="h-7 w-7 rounded-lg bg-emerald-500 text-zinc-950 flex items-center justify-center font-black text-sm">
                        A</div>
                    <span>Mi Agencia</span>
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <Link :href="route('dashboard')"
                    :class="[route().current('dashboard') ? 'bg-emerald-600 text-white dark:text-zinc-950 font-semibold shadow-lg shadow-emerald-600/20' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100', 'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200']">
                    <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </Link>

                <Link :href="route('companies.index')"
                    :class="[route().current('companies.*') ? 'bg-emerald-600 text-white dark:text-zinc-950 font-semibold shadow-lg shadow-emerald-600/20' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100', 'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 mt-2']">
                    <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Compañías
                </Link>

                <div v-if="isSuperAdmin" class="mt-2">
                    <Link :href="route('admin.tasks.index')"
                        :class="[route().current('admin.tasks.index') || route().current('admin.companies.tasks.*') ? 'bg-emerald-600 text-white dark:text-zinc-950 font-semibold shadow-lg shadow-emerald-600/20' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100', 'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200']">
                        <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Tareas
                    </Link>
                </div>

                <div v-if="isSuperAdmin" class="mt-2">
                    <button @click="isAdminOpen = !isAdminOpen"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100 transition-all duration-200">
                        <span class="flex items-center">
                            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Administración
                        </span>
                        <ChevronDown
                            :class="[isAdminOpen ? 'rotate-180' : '', 'h-4 w-4 transition-transform duration-200']" />
                    </button>
                    <div v-show="isAdminOpen" class="ml-4 mt-1 space-y-1">
                        <Link :href="route('admin.users.index')"
                            :class="[route().current('admin.users.*') ? 'bg-emerald-600 text-white dark:text-zinc-950 font-semibold' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100', 'flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200']">
                            <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Usuarios
                        </Link>
                        <Link :href="route('admin.roles.index')"
                            :class="[route().current('admin.roles.*') ? 'bg-emerald-600 text-white dark:text-zinc-950 font-semibold' : 'text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-zinc-800 hover:text-gray-900 dark:hover:text-zinc-100', 'flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200']">
                            <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Roles y Permisos
                        </Link>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header
                class="bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 flex items-center justify-end h-16 px-6 z-10 transition-colors duration-300">

                <div class="flex items-center gap-4">

                    <button @click="toggleDark()"
                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-zinc-400 dark:hover:bg-zinc-800 transition-colors">
                        <Sun v-if="isDark" class="w-5 h-5" />
                        <Moon v-else class="w-5 h-5" />
                    </button>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <span class="inline-flex rounded-md">
                                <button type="button"
                                    class="inline-flex items-center px-3 py-2 border border-gray-200 dark:border-zinc-800 text-sm leading-4 font-medium rounded-lg text-gray-700 dark:text-zinc-300 bg-white dark:bg-zinc-950 hover:text-gray-900 dark:hover:text-zinc-100 focus:outline-none transition ease-in-out duration-150">
                                    {{ $page.props.auth.user.name }}
                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Mi Perfil </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button"> Cerrar Sesión
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main
                class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-zinc-950 p-6 md:p-8 transition-colors duration-300">
                <header class="mb-6" v-if="$slots.header">
                    <div class="font-bold text-2xl text-gray-900 dark:text-zinc-100 tracking-tight">
                        <slot name="header" />
                    </div>
                </header>
                <slot />
            </main>
        </div>
    </div>
</template>
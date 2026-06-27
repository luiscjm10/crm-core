<script setup>
import { ref } from 'vue';
import { Bell, BellDot, BellOff, Circle, MessageSquareText, PlusCircle, X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { useNotifications } from '@/Composables/useNotifications.js';
import { usePushNotifications } from '@/Composables/usePushNotifications.js';

const { notifications, unreadCount, markAsRead, markAllAsRead } = useNotifications();
const { requestPermission, isSupported, isSubscribed, permissionState } = usePushNotifications();

function handleNotificationClick(notification) {
    if (!notification.is_read) {
        markAsRead(notification.id);
    }

    const url = notification.data?.url;
    if (url) {
        router.visit(url);
    }
}

function getIcon(type) {
    if (type === 'ticket.created') return PlusCircle;
    if (type === 'ticket.commented') return MessageSquareText;
    return Circle;
}

const dropdownOpen = ref(false);

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
}

function closeDropdown() {
    dropdownOpen.value = false;
}

</script>

<template>
    <div class="relative">
        <button @click="toggleDropdown"
            class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-zinc-400 dark:hover:bg-zinc-800 transition-colors">
            <BellDot v-if="unreadCount > 0" class="w-5 h-5" />
            <Bell v-else class="w-5 h-5" />
            <span v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] h-[18px]">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <div v-if="dropdownOpen"
            class="fixed inset-0 z-40"
            @click="closeDropdown" />

        <div v-if="dropdownOpen"
            class="absolute right-0 z-50 mt-2 w-80 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-zinc-800">
                <span class="text-sm font-semibold text-gray-900 dark:text-zinc-100">Notificaciones</span>
                <div class="flex items-center gap-2">
                    <button v-if="unreadCount > 0" @click="markAllAsRead"
                        class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-medium transition-colors">
                        Marcar todas leídas
                    </button>
                    <button @click="closeDropdown"
                        class="p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-zinc-300 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors">
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <div v-if="isSupported && !isSubscribed && permissionState === 'default'"
                    class="flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800/30">
                    <BellOff class="w-4 h-4 text-amber-500 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">Notificaciones del sistema</p>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5">Actívalas para recibir alertas aunque estés en otra pestaña</p>
                    </div>
                    <button @click.stop="requestPermission"
                        class="flex-shrink-0 text-[11px] font-semibold text-white bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-lg transition-colors">
                        Activar
                    </button>
                </div>

                <div v-if="notifications.length === 0"
                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-zinc-400">
                    No hay notificaciones
                </div>

                <div v-for="notification in notifications" :key="notification.id"
                    @click="handleNotificationClick(notification)"
                    class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-zinc-800/50 border-b border-gray-100 dark:border-zinc-800/50 last:border-0"
                    :class="{ 'bg-emerald-50/50 dark:bg-emerald-900/10': !notification.is_read }">

                    <div class="mt-0.5 flex-shrink-0">
                        <component :is="getIcon(notification.type)"
                            class="w-4 h-4"
                            :class="notification.type === 'ticket.created'
                                ? 'text-emerald-500'
                                : notification.type === 'ticket.commented'
                                    ? 'text-blue-500'
                                    : 'text-zinc-400'" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-zinc-100 truncate"
                            :class="{ 'font-semibold': !notification.is_read }">
                            {{ notification.data?.company_name ?? 'Sin empresa' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400 truncate mt-0.5">
                            {{ notification.data?.description ?? '' }}
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">
                            {{ notification.created_at_diff }}
                        </p>
                    </div>

                    <div v-if="!notification.is_read" class="flex-shrink-0 mt-1.5">
                        <span class="block w-2 h-2 rounded-full bg-emerald-500" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

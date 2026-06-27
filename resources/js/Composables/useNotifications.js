import { ref } from 'vue';
import axios from 'axios';
import { useIntervalFn } from '@vueuse/core';

export function useNotifications() {
    const notifications = ref([]);
    const unreadCount = ref(0);
    const loading = ref(false);

    async function fetchNotifications() {
        try {
            loading.value = true;
            const response = await axios.get(route('api.notifications.index'));
            notifications.value = response.data.notifications;
            unreadCount.value = response.data.unread_count;
        } catch (e) {
            console.error('Error fetching notifications:', e);
        } finally {
            loading.value = false;
        }
    }

    async function markAsRead(id) {
        try {
            await axios.post(route('api.notifications.read', id));
            const notif = notifications.value.find(n => n.id === id);
            if (notif) {
                notif.is_read = true;
                notif.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (e) {
            console.error('Error marking notification as read:', e);
        }
    }

    async function markAllAsRead() {
        try {
            await axios.post(route('api.notifications.read-all'));
            notifications.value.forEach(n => {
                n.is_read = true;
                n.read_at = new Date().toISOString();
            });
            unreadCount.value = 0;
        } catch (e) {
            console.error('Error marking all as read:', e);
        }
    }

    const { pause, resume } = useIntervalFn(fetchNotifications, 30000);

    fetchNotifications();

    return {
        notifications,
        unreadCount,
        loading,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        pause,
        resume,
    };
}

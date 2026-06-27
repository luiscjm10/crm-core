import { ref } from 'vue';
import axios from 'axios';

const isSupported = ref(false);
const isSubscribed = ref(false);
const permissionState = ref(Notification.permission);
const registration = ref(null);
let vapidKey = '';

export function usePushNotifications() {
    async function init(key) {
        vapidKey = key;
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return;
        }
        isSupported.value = true;

        try {
            const swReg = await navigator.serviceWorker.register('/sw.js');
            registration.value = swReg;

            permissionState.value = Notification.permission;

            if (Notification.permission === 'granted') {
                const sub = await swReg.pushManager.getSubscription();
                isSubscribed.value = !!sub;
            }
        } catch (e) {
            console.error('Push init error:', e);
        }
    }

    async function requestPermission() {
        if (!isSupported.value || !registration.value) {
            return;
        }

        try {
            const result = await Notification.requestPermission();
            permissionState.value = result;

            if (result === 'granted' && !isSubscribed.value) {
                await doSubscribe(registration.value);
            }
        } catch (e) {
            console.error('Error requesting notification permission:', e);
        }
    }

    async function doSubscribe(swReg) {
        try {
            if (!vapidKey) {
                console.warn('VAPID public key not configured');
                return;
            }

            const sub = await swReg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });

            await axios.post(route('api.push.subscribe'), sub.toJSON());
            isSubscribed.value = true;
        } catch (e) {
            console.error('Error subscribing to push:', e);
        }
    }

    return { init, requestPermission, isSupported, isSubscribed, permissionState, registration };
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

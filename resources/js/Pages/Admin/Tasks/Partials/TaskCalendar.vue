<script setup>
import { ref, computed } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

const props = defineProps({
    tasks: Object,
});

const emit = defineEmits(['open-task']);

const statusColors = {
    planned: '#a1a1aa',
    todo: '#3b82f6',
    in_progress: '#f59e0b',
    done: '#10b981',
    cancelled: '#ef4444',
};

const typeIconSvgs = {
    general: '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22V4a1 1 0 0 1 .4-.8A6 6 0 0 1 8 2c3 0 5 2 7.333 2q2 0 3.067-.8A1 1 0 0 1 20 4v10a1 1 0 0 1-.4.8A6 6 0 0 1 16 16c-3 0-5-2-8-2a6 6 0 0 0-4 1.528"/></svg>',
    visit: '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
    maintenance: '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"/></svg>',
    development: '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/></svg>',
};

const selectedStatuses = ref([]);

const statusOptions = [
    { value: 'planned', label: 'Planeadas' },
    { value: 'todo', label: 'Por hacer' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'done', label: 'Completadas' },
    { value: 'cancelled', label: 'Canceladas' },
];

const events = computed(() =>
    props.tasks.data
        .filter((task) => task.due_date)
        .filter((task) => selectedStatuses.value.length === 0 || selectedStatuses.value.includes(task.status))
        .map((task) => ({
            id: task.id.toString(),
            title: task.name,
            start: task.due_date,
            allDay: true,
            backgroundColor: statusColors[task.status] || statusColors.planned,
            borderColor: statusColors[task.status] || statusColors.planned,
            textColor: '#ffffff',
            extendedProps: { task, iconSvg: typeIconSvgs[task.type] || '' },
        }))
);

const toggleStatus = (status) => {
    const idx = selectedStatuses.value.indexOf(status);
    if (idx === -1) {
        selectedStatuses.value = [...selectedStatuses.value, status];
    } else {
        selectedStatuses.value = selectedStatuses.value.filter((s) => s !== status);
    }
};

const handleEventClick = (info) => {
    const task = info.event.extendedProps.task;
    emit('open-task', task);
};

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek',
    },
    locale: esLocale,
    height: 'auto',
    dayMaxEvents: 3,
    eventTimeDisplay: false,
    noEventsText: 'Sin tareas',
    moreLinkText: (num) => `+${num} más`,
    buttonText: {
        today: 'Hoy',
        month: 'Mes',
        week: 'Semana',
    },
    eventClick: handleEventClick,
    eventContent: (arg) => {
        const svg = arg.event.extendedProps.iconSvg;
        return svg
            ? { html: `<span style="display:inline-flex;align-items:center;gap:2px">${svg}</span> ${arg.event.title}` }
            : arg.event.title;
    },
    events: events.value,
}));
</script>

<template>
    <div class="mt-6 space-y-4">
        <div class="flex items-center gap-2 flex-wrap">
            <button @click="selectedStatuses = []"
                :class="['px-3 py-1 text-xs font-medium rounded-full border transition-colors',
                    selectedStatuses.length === 0
                        ? 'bg-primary text-primary-foreground border-primary'
                        : 'bg-muted text-muted-foreground border-transparent hover:bg-muted/80']">
                Todas
            </button>
            <button v-for="opt in statusOptions" :key="opt.value"
                @click="toggleStatus(opt.value)"
                :class="['px-3 py-1 text-xs font-medium rounded-full border transition-all',
                    selectedStatuses.includes(opt.value)
                        ? 'border-current ring-1 ring-current/30'
                        : 'border-transparent opacity-50 hover:opacity-80']"
                :style="{
                    color: statusColors[opt.value],
                    backgroundColor: statusColors[opt.value] + '1A',
                }">
                {{ opt.label }}
            </button>
        </div>
        <FullCalendar :options="calendarOptions" />
    </div>
</template>

<style>
.fc {
    --fc-page-bg-color: #ffffff;
    --fc-border-color: #e5e7eb;
    --fc-button-text-color: #374151;
    --fc-button-bg-color: #f3f4f6;
    --fc-button-border-color: #d1d5db;
    --fc-button-hover-bg-color: #e5e7eb;
    --fc-button-hover-border-color: #9ca3af;
    --fc-button-active-bg-color: #d1d5db;
    --fc-today-bg-color: #f0fdf4;
    --fc-neutral-bg-color: #fafafa;
    --fc-list-event-hover-bg-color: #f9fafb;
    --fc-event-text-color: #ffffff;
    --fc-event-border-color: currentColor;
}

.dark .fc {
    --fc-page-bg-color: #09090b;
    --fc-border-color: #27272a;
    --fc-button-text-color: #f4f4f5;
    --fc-button-bg-color: #27272a;
    --fc-button-border-color: #3f3f46;
    --fc-button-hover-bg-color: #3f3f46;
    --fc-button-hover-border-color: #52525b;
    --fc-button-active-bg-color: #52525b;
    --fc-today-bg-color: #052e16;
    --fc-neutral-bg-color: #18181b;
    --fc-list-event-hover-bg-color: #18181b;
}

.fc .fc-toolbar-title {
    font-size: 1.125rem;
    font-weight: 600;
}

.fc .fc-button {
    font-size: 0.75rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.5rem;
    font-weight: 500;
    text-transform: capitalize;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background-color: #d1d5db;
    border-color: #9ca3af;
    color: #111827;
}

.dark .fc .fc-button-primary:not(:disabled).fc-button-active,
.dark .fc .fc-button-primary:not(:disabled):active {
    background-color: #52525b;
    border-color: #71717a;
    color: #fafafa;
}

.fc .fc-daygrid-day-number {
    font-size: 0.75rem;
    color: #52525b;
    padding: 0.375rem;
}

.dark .fc .fc-daygrid-day-number {
    color: #a1a1aa;
}

.fc .fc-daygrid-more-link {
    font-size: 0.6875rem;
    color: #10b981;
}

.fc .fc-event {
    border-radius: 0.375rem;
    padding: 0.0625rem 0.25rem;
    font-size: 0.6875rem;
    cursor: pointer;
    border-width: 0;
    font-weight: 500;
}

.fc .fc-day-other {
    opacity: 0.4;
}

.fc .fc-scrollgrid {
    border-radius: 0.5rem;
    overflow: hidden;
}

.fc .fc-col-header-cell {
    padding: 0.5rem 0;
}

.fc .fc-col-header-cell-cushion {
    font-size: 0.75rem;
    font-weight: 600;
    color: #52525b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.dark .fc .fc-col-header-cell-cushion {
    color: #a1a1aa;
}

.fc .fc-day-today .fc-daygrid-day-frame {
    background-color: var(--fc-today-bg-color);
}
</style>

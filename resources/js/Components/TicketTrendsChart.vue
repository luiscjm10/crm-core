<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { useDark } from '@vueuse/core';
import { ChartBar } from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const period = ref('week');
const status = ref('all');
const companyId = ref('all');
const labels = ref([]);
const values = ref([]);
const total = ref(0);
const rangeLabel = ref('');
const loading = ref(false);

const isDark = useDark();

const statusOptions = [
    { value: 'all', label: 'Todas' },
    { value: 'open', label: 'Abiertas' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'closed', label: 'Cerradas' },
];

const periodOptions = [
    { value: 'week', label: 'Esta semana' },
    { value: 'month', label: 'Este mes' },
    { value: 'prev_month', label: 'Mes anterior' },
];

const companies = computed(() => usePage().props.companies ?? []);

const fetchTrends = async () => {
    loading.value = true;
    try {
        const params = { period: period.value, status: status.value };
        if (companyId.value !== 'all') params.company_id = companyId.value;
        const res = await fetch(
            route('dashboard.trends', params),
            { headers: { Accept: 'application/json' } }
        );
        if (!res.ok) throw new Error('Error al cargar tendencias');
        const json = await res.json();
        labels.value = json.labels ?? [];
        values.value = json.data ?? [];
        total.value = json.total ?? 0;
        rangeLabel.value = json.range_label ?? '';
    } catch {
        labels.value = [];
        values.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchTrends);
watch([period, status, companyId], fetchTrends);

const chartData = computed(() => ({
    labels: labels.value,
    datasets: [
        {
            label: 'Solicitudes',
            data: values.value,
            backgroundColor: '#10b981',
            hoverBackgroundColor: '#059669',
            borderRadius: 6,
            maxBarThickness: 28,
        },
    ],
}));

const chartOptions = computed(() => {
    const tick = isDark.value ? '#a1a1aa' : '#71717a';
    const grid = isDark.value ? 'rgba(63,63,70,.35)' : 'rgba(228,228,231,.9)';
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: isDark.value ? '#09090b' : '#ffffff',
                titleColor: isDark.value ? '#fafafa' : '#111827',
                bodyColor: isDark.value ? '#fafafa' : '#111827',
                borderColor: isDark.value ? '#27272a' : '#e4e4e7',
                borderWidth: 1,
                padding: 10,
                displayColors: false,
            },
        },
        scales: {
            x: { ticks: { color: tick, font: { size: 11 } }, grid: { display: false } },
            y: {
                beginAtZero: true,
                ticks: { color: tick, precision: 0, font: { size: 11 } },
                grid: { color: grid },
            },
        },
    };
});

const selectClass =
    'w-full rounded-md border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-950 px-3 py-2 text-sm text-gray-900 dark:text-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500';
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <ChartBar class="h-5 w-5" />
                </div>
                <div>
                    <CardTitle>Tendencia de solicitudes</CardTitle>
                    <CardDescription>{{ rangeLabel || 'Por fecha de solicitud' }}</CardDescription>
                </div>
            </div>
            <span
                class="inline-flex items-center rounded-full border border-emerald-600/30 bg-emerald-600/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-400">
                {{ total }} total
            </span>
        </CardHeader>
        <CardContent>
            <div class="flex flex-wrap gap-3 mb-4">
                <div class="min-w-[160px] flex-1 sm:max-w-[220px]">
                    <label for="trend-status"
                        class="block text-xs font-medium text-gray-500 dark:text-zinc-400 mb-1">Estado</label>
                    <select id="trend-status" v-model="status" :class="selectClass">
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[160px] flex-1 sm:max-w-[220px]">
                    <label for="trend-period"
                        class="block text-xs font-medium text-gray-500 dark:text-zinc-400 mb-1">Periodo</label>
                    <select id="trend-period" v-model="period" :class="selectClass">
                        <option v-for="opt in periodOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[160px] flex-1 sm:max-w-[220px]">
                    <label for="trend-company"
                        class="block text-xs font-medium text-gray-500 dark:text-zinc-400 mb-1">Compañía</label>
                    <select id="trend-company" v-model="companyId" :class="selectClass">
                        <option value="all">Todas</option>
                        <option v-for="company in companies" :key="company.id" :value="company.id">
                            {{ company.name }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="relative h-64">
                <div v-if="loading"
                    class="absolute inset-0 flex items-center justify-center text-sm text-muted-foreground">
                    Cargando…
                </div>
                <Bar v-else :data="chartData" :options="chartOptions" />
            </div>
        </CardContent>
    </Card>
</template>

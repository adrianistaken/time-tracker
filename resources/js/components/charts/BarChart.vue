<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

interface Props {
    labels: string[];
    data: number[];
    colors: string[];
    horizontal?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    horizontal: true,
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            backgroundColor: props.colors,
            borderRadius: 4,
            borderSkipped: false,
        },
    ],
}));

const chartOptions = computed(() => ({
    indexAxis: props.horizontal ? ('y' as const) : ('x' as const),
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(24, 24, 27, 0.95)',
            titleColor: '#fff',
            bodyColor: '#a1a1aa',
            borderColor: 'rgba(63, 63, 70, 0.5)',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: (context: { parsed: { x: number; y: number } }) => {
                    const hours = props.horizontal ? context.parsed.x : context.parsed.y;
                    return `${hours.toFixed(1)} hours`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: {
                color: 'rgba(63, 63, 70, 0.3)',
                drawBorder: false,
            },
            ticks: {
                color: '#71717a',
                font: {
                    size: 11,
                },
            },
            border: {
                display: false,
            },
        },
        y: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#a1a1aa',
                font: {
                    size: 12,
                },
            },
            border: {
                display: false,
            },
        },
    },
}));
</script>

<template>
    <div class="h-full w-full">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler,
} from 'chart.js';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Filler
);

interface Props {
    labels: string[];
    data: number[];
    color?: string;
    fill?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    color: '#6366f1',
    fill: true,
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            borderColor: props.color,
            backgroundColor: props.fill
                ? `${props.color}20`
                : 'transparent',
            fill: props.fill,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: props.color,
            pointBorderColor: '#18181b',
            pointBorderWidth: 2,
        },
    ],
}));

const chartOptions = computed(() => ({
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
                label: (context: { parsed: { y: number } }) => {
                    return `${context.parsed.y.toFixed(1)} hours`;
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
            beginAtZero: true,
        },
    },
}));
</script>

<template>
    <div class="h-full w-full">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

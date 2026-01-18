import { computed, onMounted, onUnmounted, ref } from 'vue';

/**
 * Composable for managing a live timer that calculates elapsed time from a start timestamp.
 * The timer persists across page refreshes because it reads from the server-provided start time.
 */
export function useTimer(startedAt: string) {
    const elapsedSeconds = ref(0);
    let intervalId: ReturnType<typeof setInterval> | null = null;

    const startTime = new Date(startedAt).getTime();

    const updateElapsedTime = () => {
        const now = Date.now();
        elapsedSeconds.value = Math.floor((now - startTime) / 1000);
    };

    const formattedTime = computed(() => {
        const totalSeconds = elapsedSeconds.value;
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        return {
            hours: hours.toString().padStart(2, '0'),
            minutes: minutes.toString().padStart(2, '0'),
            seconds: seconds.toString().padStart(2, '0'),
            full: `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`,
        };
    });

    const start = () => {
        if (intervalId) return;

        updateElapsedTime();
        intervalId = setInterval(updateElapsedTime, 1000);
    };

    const stop = () => {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    onMounted(() => {
        start();
    });

    onUnmounted(() => {
        stop();
    });

    return {
        elapsedSeconds,
        formattedTime,
        start,
        stop,
    };
}

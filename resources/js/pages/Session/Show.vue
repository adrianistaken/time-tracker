<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTimer } from '@/composables/useTimer';
import type { ActiveSession } from '@/types';

import { stop } from '@/actions/App/Http/Controllers/SessionController';

interface Props {
    session: ActiveSession;
}

const props = defineProps<Props>();

const { formattedTime } = useTimer(props.session.started_at);

const showNoteModal = ref(false);
const form = useForm({
    note: '',
});

const handleStopClick = () => {
    showNoteModal.value = true;
};

const handleSubmitStop = () => {
    form.submit(stop(props.session.id));
};

const handleSkipNote = () => {
    form.note = '';
    form.submit(stop(props.session.id));
};
</script>

<template>
    <Head :title="`${session.project.name} - Focus Mode`" />

    <!-- Focus Mode: Full screen, minimal, calm -->
    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-zinc-950"
    >
        <!-- Subtle animated background glow -->
        <div
            class="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div
                class="animate-pulse-slow absolute left-1/2 top-1/2 h-[600px] w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-full opacity-20 blur-3xl"
                :style="{
                    backgroundColor: session.project.color,
                }"
            />
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col items-center gap-12 px-6">
            <!-- Project indicator -->
            <div class="flex items-center gap-3">
                <div
                    class="h-3 w-3 animate-pulse rounded-full"
                    :style="{ backgroundColor: session.project.color }"
                />
                <span class="text-lg font-medium tracking-wide text-zinc-400">
                    {{ session.project.name }}
                </span>
            </div>

            <!-- Timer display -->
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-baseline gap-1 font-mono text-8xl font-light tracking-tight text-white tabular-nums md:text-9xl">
                    <span>{{ formattedTime.hours }}</span>
                    <span class="animate-pulse text-zinc-500">:</span>
                    <span>{{ formattedTime.minutes }}</span>
                    <span class="animate-pulse text-zinc-500">:</span>
                    <span>{{ formattedTime.seconds }}</span>
                </div>
                <span class="text-sm uppercase tracking-widest text-zinc-600">
                    Time elapsed
                </span>
            </div>

            <!-- Stop button -->
            <Button
                size="lg"
                variant="destructive"
                class="group relative h-16 w-48 text-lg font-semibold shadow-2xl transition-all duration-300 hover:scale-105 hover:shadow-red-500/25"
                @click="handleStopClick"
            >
                <span class="relative z-10">Stop</span>
                <div
                    class="absolute inset-0 rounded-md bg-gradient-to-t from-red-700 to-red-500 opacity-0 transition-opacity group-hover:opacity-100"
                />
            </Button>

            <!-- Dashboard link -->
            <a
                href="/dashboard"
                class="mt-8 text-sm text-zinc-600 transition-colors hover:text-zinc-400"
            >
                View Dashboard →
            </a>
        </div>
    </div>

    <!-- Note Modal -->
    <Dialog v-model:open="showNoteModal">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Session Complete</DialogTitle>
                <DialogDescription>
                    Add an optional note about what you worked on.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmitStop" class="space-y-4">
                <div class="space-y-2">
                    <Label for="note">Note (optional)</Label>
                    <Input
                        id="note"
                        v-model="form.note"
                        placeholder="What did you work on?"
                        autofocus
                    />
                </div>

                <DialogFooter class="gap-2 sm:gap-0">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="handleSkipNote"
                            :disabled="form.processing"
                        >
                            Skip
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        Save & Stop
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style>
@keyframes pulse-slow {
    0%,
    100% {
        opacity: 0.15;
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        opacity: 0.25;
        transform: translate(-50%, -50%) scale(1.1);
    }
}

.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}
</style>

<script setup lang="ts">
import { Palette } from 'lucide-vue-next';
import { ref } from 'vue';

import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useAccentColor } from '@/composables/useAccentColor';

interface Props {
    colors: string[];
}

defineProps<Props>();

const { accentColor, updateAccentColor } = useAccentColor();
const isOpen = ref(false);

const handleColorSelect = (color: string) => {
    updateAccentColor(color);
    isOpen.value = false;
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <button
                class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-zinc-800"
                :style="{ color: accentColor }"
                title="Change accent color"
            >
                <Palette class="h-4 w-4" />
            </button>
        </PopoverTrigger>
        <PopoverContent
            align="start"
            :side-offset="8"
            class="w-auto border-zinc-800 bg-zinc-900 p-3"
        >
            <div class="space-y-2">
                <p class="text-xs font-medium text-zinc-400">Accent Color</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="color in colors"
                        :key="color"
                        type="button"
                        class="h-7 w-7 rounded-md transition-transform hover:scale-110"
                        :class="{
                            'ring-2 ring-white ring-offset-2 ring-offset-zinc-900': accentColor === color,
                        }"
                        :style="{ backgroundColor: color }"
                        @click="handleColorSelect(color)"
                    />
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

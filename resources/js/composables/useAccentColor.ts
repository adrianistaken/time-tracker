import { onMounted, ref, watch } from 'vue';

// Default accent color (indigo)
const DEFAULT_ACCENT_COLOR = '#6366f1';

// Shared reactive state across components
const accentColor = ref<string>(DEFAULT_ACCENT_COLOR);

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAccentColor = (): string | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('accentColor');
};

/**
 * Initialize the accent color from stored preference.
 * Call this early in app initialization.
 */
export function initializeAccentColor(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const savedColor = getStoredAccentColor();
    if (savedColor) {
        accentColor.value = savedColor;
        applyAccentColorToCSS(savedColor);
    }
}

/**
 * Apply the accent color to CSS custom properties for use in Tailwind.
 */
function applyAccentColorToCSS(color: string): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.style.setProperty('--accent-color', color);
    document.documentElement.style.setProperty('--accent-color-20', `${color}33`);
    document.documentElement.style.setProperty('--accent-color-50', `${color}80`);
}

/**
 * Composable for managing the app's accent color.
 */
export function useAccentColor() {
    onMounted(() => {
        const savedColor = getStoredAccentColor();
        if (savedColor) {
            accentColor.value = savedColor;
            applyAccentColorToCSS(savedColor);
        }
    });

    // Watch for changes and apply to CSS
    watch(accentColor, (newColor) => {
        applyAccentColorToCSS(newColor);
    });

    function updateAccentColor(color: string): void {
        accentColor.value = color;

        // Store in localStorage for client-side persistence
        localStorage.setItem('accentColor', color);

        // Store in cookie for potential SSR usage
        setCookie('accentColor', color);

        // Apply to CSS
        applyAccentColorToCSS(color);
    }

    return {
        accentColor,
        updateAccentColor,
    };
}

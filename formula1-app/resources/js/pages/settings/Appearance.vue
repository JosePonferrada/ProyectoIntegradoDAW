<script setup lang="ts">
import { Head, usePage, router } from '@inertiajs/vue3'; // Mantener router si se usa para otras cosas, sino quitar
import { ref, watch, onMounted, onUnmounted } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Label } from '@/components/ui/label';
// import { Button } from '@/components/ui/button'; // Quitar si no se usa el botón
import { Sun, Moon, Laptop, CheckCircle2 } from 'lucide-vue-next'; // Quitar LoaderCircle si no se usa el botón

const page = usePage();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance Settings',
        href: '/settings/appearance',
    },
];

type Theme = 'light' | 'dark' | 'system';

const currentTheme = ref<Theme>((page.props.auth as any)?.user?.appearance_preference || localStorage.getItem('theme') as Theme || 'system');
// const initialThemeFromServer = ref<Theme>((page.props.auth as any)?.user?.appearance_preference || 'system'); // No necesario si no hay botón de guardar
// const hasChanges = ref(false); // No necesario si no hay botón de guardar
// const isSaving = ref(false); // No necesario si no hay botón de guardar

watch(currentTheme, (newTheme, oldTheme) => {
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
    // Si quieres persistir en backend automáticamente al cambiar:
    // if (newTheme !== oldTheme && page.props.auth) { // Evitar llamada inicial si es igual
    //     persistThemePreference(newTheme);
    // }
});

const applyTheme = (theme: Theme) => {
    const root = document.documentElement;
    if (theme === 'system') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', prefersDark);
    } else {
        root.classList.toggle('dark', theme === 'dark');
    }
    if (theme === 'light') {
        root.classList.remove('dark');
    }
};

onMounted(() => {
    applyTheme(currentTheme.value);
});

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const handleSystemThemeChange = (e: MediaQueryListEvent) => {
    if (currentTheme.value === 'system') {
        document.documentElement.classList.toggle('dark', e.matches);
    }
};

onMounted(() => {
    mediaQuery.addEventListener('change', handleSystemThemeChange);
});

onUnmounted(() => {
    mediaQuery.removeEventListener('change', handleSystemThemeChange);
});

const themeOptions = [
    { value: 'light' as Theme, label: 'Light Mode', icon: Sun, description: "Bright and clear, like a sunny race day." },
    { value: 'dark' as Theme, label: 'Dark Mode', icon: Moon, description: "Sleek and focused, for night races and late-night stats." },
    { value: 'system' as Theme, label: 'System Default', icon: Laptop, description: "Follows your device's current theme settings." },
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance Settings - F1 Account" />

        <SettingsLayout>
            <div class="space-y-8">
                <HeadingSmall 
                    title="Tune Your Cockpit View" 
                    description="Customize the look and feel of your F1 Stats dashboard."
                    class="text-gray-700 dark:text-red-500"
                />
                
                <div class="space-y-6 max-w-xl p-6 bg-white dark:bg-gray-800/50 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-1">Select Your Theme</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Choose how you want the application to look. Your preference is saved automatically.</p>

                    <div class="space-y-4">
                        <label 
                            v-for="option in themeOptions" 
                            :key="option.value"
                            :for="`theme-${option.value}`" 
                            :class="[
                                'flex items-center p-4 rounded-md border transition-all duration-150 ease-in-out cursor-pointer relative group',
                                'bg-gray-50 border-gray-300 hover:border-gray-400 hover:bg-gray-100',
                                'dark:bg-gray-700/30 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-700/50',
                                currentTheme === option.value ? 
                                    'border-red-500 ring-2 ring-red-500 bg-red-500/10 dark:bg-red-600/20 dark:border-red-500' : 
                                    ''
                            ]"
                        >
                            <input 
                                type="radio"
                                :id="`theme-${option.value}`"
                                name="theme_option"
                                :value="option.value"
                                v-model="currentTheme"
                                class="sr-only" 
                            />
                            
                            <component :is="option.icon" :class="[
                                'h-6 w-6 mr-3 flex-shrink-0', 
                                currentTheme === option.value ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300'
                                ]" />
                            <div class="flex-grow">
                                <span :class="[
                                    'font-medium', 
                                    currentTheme === option.value ? 'text-red-700 dark:text-red-100' : 'text-gray-800 dark:text-gray-200 group-hover:text-gray-900 dark:group-hover:text-white'
                                    ]">{{ option.label }}</span>
                                <p :class="[
                                    'text-xs', 
                                    currentTheme === option.value ? 'text-red-600/80 dark:text-red-200/80' : 'text-gray-600 dark:text-gray-400/80 group-hover:text-gray-700 dark:group-hover:text-gray-300/80'
                                    ]">{{ option.description }}</p>
                            </div>
                            <CheckCircle2 v-if="currentTheme === option.value" 
                                :class="[
                                    'h-6 w-6 text-red-600 dark:text-red-400 absolute inset-y-0 right-4 my-auto transition-opacity duration-150 opacity-0',
                                    currentTheme === option.value ? 'opacity-100' : 'group-hover:opacity-50'
                                ]" />
                        </label>
                    </div>

                    <!-- Botón de Guardar Eliminado -->
                    <!-- 
                    <div class="pt-6 flex justify-end" v-if="page.props.auth">
                        <Button 
                            @click="saveAppearancePreference" 
                            :disabled="!hasChanges || isSaving"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <LoaderCircle v-if="isSaving" class="animate-spin h-4 w-4 mr-2" />
                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                    -->
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

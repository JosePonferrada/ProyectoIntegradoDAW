<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle, X } from 'lucide-vue-next'; // Importar X para el botón de cerrar
import { type BreadcrumbItem } from '@/types';
// Quitamos useToast si vamos a usar el sistema manual
// import { useToast } from '@/components/ui/toast/use-toast';

// const { toast } = useToast(); // Comentado

// Estado para la notificación personalizada
const notification = ref({
    show: false,
    message: '',
    type: 'success' as 'success' | 'error', // Tipado para el tipo de notificación
});

// Función para mostrar notificación
const showNotification = (message: string, type: 'success' | 'error' = 'success', duration: number = 4000) => {
    notification.value = {
        show: true,
        message,
        type
    };
    setTimeout(() => {
        notification.value.show = false;
    }, duration);
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password Settings',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showNotification('Pit Stop Confirmed! Your password has been successfully updated.', 'success');
        },
        onError: (errors: Record<string, string>) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            } else if (errors.current_password) {
                currentPasswordInput.value?.focus();
            }

            const errorKeys = Object.keys(errors);
            const firstErrorMessage = errorKeys.length > 0 ? errors[errorKeys[0]] : 'Please check the form for errors.';
            
            showNotification(`Update Failed: ${firstErrorMessage}`, 'error');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password Settings - F1 Account" />

        <!-- Notificación Personalizada Flotante -->
        <div
            v-if="notification.show"
            :class="[

                'fixed top-5 right-5 p-4 rounded-md shadow-lg z-[100] transition-all duration-300 ease-in-out border',
                'max-w-sm w-full',
                notification.type === 'success' ? 
                    'bg-green-100 border-green-400 text-green-700 dark:bg-green-500 dark:border-green-600 dark:text-white' : 
                    'bg-red-100 border-red-400 text-red-700 dark:bg-red-500 dark:border-red-600 dark:text-white'
            ]"
            role="alert"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">{{ notification.message }}</span>
                <button @click="notification.show = false" 
                    :class="[

                        'ml-3 hover:opacity-75',
                        notification.type === 'success' ? 'text-green-600 dark:text-white' : 'text-red-600 dark:text-white'
                    ]"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
        </div>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall 
                    title="Secure Your Pit Lane Access" 
                    description="Keep your F1 Team credentials secure. Update your password regularly with a strong, unique code."
                    class="text-gray-700 dark:text-red-500"
                />

                <form @submit.prevent="updatePassword" 
                    class="space-y-6 max-w-xl p-6 bg-white dark:bg-gray-800/50 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
                    <div class="grid gap-2">
                        <Label for="current_password" class="text-gray-700 dark:text-gray-300 font-semibold">Current Password (Old Access Code)</Label>
                        <Input
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="mt-1 block w-full bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/50 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white dark:placeholder-gray-500"
                            autocomplete="current-password"
                            placeholder="Enter your current password"
                            required
                        />
                        <InputError :message="form.errors.current_password" class="mt-2 text-red-600 dark:text-red-400" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="text-gray-700 dark:text-gray-300 font-semibold">New Password (New Secure Code)</Label>
                        <Input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/50 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white dark:placeholder-gray-500"
                            autocomplete="new-password"
                            placeholder="Enter your new strong password"
                            required
                        />
                        <InputError :message="form.errors.password" class="mt-2 text-red-600 dark:text-red-400" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-gray-700 dark:text-gray-300 font-semibold">Confirm New Password (Verify Code)</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full bg-gray-50 border-gray-300 text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/50 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white dark:placeholder-gray-500"
                            autocomplete="new-password"
                            placeholder="Confirm your new password"
                            required
                        />
                        <InputError :message="form.errors.password_confirmation" class="mt-2 text-red-600 dark:text-red-400" />
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <Button 
                            :disabled="form.processing" 
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 text-white font-bold py-2.5 px-6 rounded-md shadow-lg transform transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 disabled:opacity-60"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" />
                            Update Password
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

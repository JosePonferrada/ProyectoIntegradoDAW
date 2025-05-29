<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// No usaremos AuthLayout aquí
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
    status?: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            // No resetear email y token, solo contraseñas
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Reset Password - F1 Access" />

    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-gradient-to-b from-gray-900 to-gray-800 text-gray-100">
        <!-- Logo -->
        <div class="flex justify-center w-full mb-8 sm:max-w-md">
            <div class="p-4 sm:p-6">
                 <Link href="/">
                    <img src="/img/logo-f1.png" alt="F1 Logo" class="h-32 sm:h-40 mx-auto" />
                </Link>
            </div>
        </div>

        <div class="w-full sm:max-w-md px-6 py-8 bg-gray-800 shadow-xl overflow-hidden sm:rounded-lg border-t-4 border-red-600">
            <h1 class="text-2xl sm:text-3xl font-bold text-white text-center mb-2">
                New Set of Tyres (Password)!
            </h1>
            <p class="text-sm text-gray-300 text-center mb-6">
                Your new access credentials are ready for fitting. Set your new password to get back on track.
            </p>

            <!-- Status Message -->
            <div v-if="props.status" class="mb-4 font-medium text-sm text-red-400 bg-red-900/30 border border-red-500/30 rounded-md p-3 text-center">
                {{ props.status }}
            </div>
            <div v-if="form.recentlySuccessful" class="mb-4 font-medium text-sm text-green-400 bg-green-900/30 border border-green-500/30 rounded-md p-3 text-center">
                Password reset successfully! You can now log in.
            </div>
             <div v-if="form.hasErrors && !props.status" class="mb-4 font-medium text-sm text-red-400 bg-red-900/30 border border-red-500/30 rounded-md p-3 text-center">
                An error occurred. Please check the details below.
            </div>


            <form @submit.prevent="submit">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email" class="text-gray-200">Driver Email</Label>
                        <Input 
                            id="email" 
                            type="email" 
                            name="email" 
                            autocomplete="email" 
                            v-model="form.email" 
                            class="mt-1 block w-full bg-gray-700 border-gray-600 text-white placeholder-gray-500 focus:border-red-500 focus:ring-red-500" 
                            readonly 
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="text-gray-200">New Password (Secure Pit Lane Code)</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            v-model="form.password"
                            class="mt-1 block w-full bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-red-500 focus:ring-red-500"
                            autofocus
                            placeholder="Enter your new secure password"
                            required
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-gray-200">Confirm New Password (Double Check)</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autocomplete="new-password"
                            v-model="form.password_confirmation"
                            class="mt-1 block w-full bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-red-500 focus:ring-red-500"
                            placeholder="Confirm your new password"
                            required
                        />
                        <InputError :message="form.errors.password_confirmation" class="mt-2" />
                    </div>

                    <Button 
                        type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 text-white font-semibold py-2.5 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-70" 
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                        Set New Password & Hit the Track
                    </Button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <Link :href="route('login')" class="text-sm text-red-400 hover:text-red-300 hover:underline transition-colors">
                    ← Back to the Starting Grid (Login)
                </Link>
            </div>
            
            <!-- Decoración temática F1 -->
            <div class="mt-8 pt-6 border-t border-gray-700 flex justify-center items-center">
                <div class="flex-shrink-0 text-white opacity-70">🏁</div>
                <div class="w-full h-2 mx-2 bg-gray-700 relative overflow-hidden rounded-full">
                    <div class="absolute h-full w-1/2 bg-gradient-to-r from-red-600 via-red-500 to-red-600 animate-pulse"></div>
                </div>
                <div class="flex-shrink-0 text-white opacity-70">🏁</div>
            </div>
        </div>
        <div class="py-4">
            <p class="text-center text-xs text-gray-500">
                &copy; {{ new Date().getFullYear() }} Formula 1 Access. All rights reserved.
            </p>
        </div>
    </div>
</template>

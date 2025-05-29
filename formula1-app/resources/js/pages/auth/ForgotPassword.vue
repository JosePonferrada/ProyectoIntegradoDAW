<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm, Link } from '@inertiajs/vue3'; // Asegúrate de importar Link
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    status?: string;
}

const props = defineProps<Props>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password - F1 Access" />

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
                Pit Stop for Password Reset!
            </h1>
            <p class="text-sm text-gray-300 text-center mb-6">
                Lost your pit lane pass (password)? No worries! Enter your email, and we'll send a new access code to get you back in the race.
            </p>

            <!-- Status Message -->
            <div v-if="props.status" class="mb-4 font-medium text-sm text-green-400 bg-green-900/30 border border-green-500/30 rounded-md p-3 text-center">
                {{ props.status }}
            </div>

            <form @submit.prevent="submit">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email" class="text-gray-200">Your Email (Radio Check)</Label>
                        <Input 
                            id="email" 
                            type="email" 
                            name="email" 
                            autocomplete="email" 
                            v-model="form.email" 
                            class="mt-1 block w-full bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-red-500 focus:ring-red-500" 
                            autofocus
                            placeholder="Enter your team email"
                            required
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <Button 
                        type="submit" 
                        class="w-full bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 text-white font-semibold py-2.5 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-70" 
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                        Send Reset Link to Paddock
                    </Button>
                </div>
            </form>

            <!-- Back to Login -->
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

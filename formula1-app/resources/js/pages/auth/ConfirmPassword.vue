<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const passwordInput = ref<HTMLInputElement | null>(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            // No es necesario resetear el password aquí si la confirmación es exitosa,
            // ya que usualmente redirige o la página se recarga para la acción protegida.
            // Si falla, el error se mostrará y el campo permanecerá.
            // form.reset(); // Puedes descomentar si prefieres resetear siempre.
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password'); // Resetea solo el campo de contraseña en error
                passwordInput.value?.focus();
            }
        }
    });
};
</script>

<template>
    <Head title="Confirm Password - Secure Pit Stop" />

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
                Secure Pit Stop!
            </h1>
            <p class="text-sm text-gray-300 text-center mb-6">
                This is a secure area of the application. Please confirm your password to proceed to the next lap.
            </p>

            <form @submit.prevent="submit">
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="password" class="text-gray-200">Your Password (Access Code)</Label>
                        <Input
                            id="password"
                            ref="passwordInput"
                            type="password"
                            class="mt-1 block w-full bg-gray-700 border-gray-600 text-white placeholder-gray-400 focus:border-red-500 focus:ring-red-500"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            autofocus
                            placeholder="Enter your current password"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <Button
                        type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 text-white font-semibold py-2.5 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-70"
                        :disabled="form.processing"
                    >
                        <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                        Confirm & Proceed
                    </Button>
                </div>
            </form>

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

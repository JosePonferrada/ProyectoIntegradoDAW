<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { AlertCircle, Check, LoaderCircle } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
const showBanner = computed(() => user.value && !user.value.email_verified_at);

const form = useForm({});

const resendVerification = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <div v-if="showBanner" class="bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800">
        <div class="max-w-screen-xl mx-auto py-3 px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between flex-wrap">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-red-100 dark:bg-red-800">
                        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-300" />
                    </span>
                    <p class="ml-3 font-medium text-red-800 dark:text-red-200 truncate">
                        <span class="md:hidden">Please verify your email</span>
                        <span class="hidden md:inline">
                            Your email address is not verified. Please check your inbox or request a new verification link.
                        </span>
                    </p>
                </div>
                
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto">
                    <div class="rounded-md shadow-sm">
                        <button
                            @click="resendVerification"
                            :disabled="form.processing"
                            class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-red-800 dark:text-red-200 bg-red-100 dark:bg-red-800 hover:bg-red-200 dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                        >
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                            <Check v-else-if="form.recentlySuccessful" class="h-4 w-4 mr-2" />
                            <span v-if="form.recentlySuccessful">Sent!</span>
                            <span v-else>Resend verification</span>
                        </button>
                    </div>
                </div>
                
                <div class="order-2 flex-shrink-0 sm:order-3 sm:ml-3">
                    <Link
                        :href="route('verification.notice')"
                        class="text-red-800 dark:text-red-200 hover:text-red-600 dark:hover:text-red-100"
                    >
                        <span class="sr-only">Details</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => form.recentlySuccessful);
</script>

<template>
  <Head title="Email verification" />

  <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-gradient-to-b from-gray-900 to-gray-800">
      <!-- Logo -->
      <div class="flex justify-center w-full mb-8 sm:max-w-md">
          <div class="p-4 sm:p-6">
              <img src="/img/logo-f1.png" alt="F1 Logo" class="h-40 mx-auto" />
          </div>
      </div>

      <div class="w-full sm:max-w-md px-6 py-8 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border-t-4 border-red-600">
          <h1 class="text-2xl font-bold text-white text-center mb-6">Verify Your Email</h1>
          
          <div class="mb-4 text-sm text-gray-300 text-center">
              Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
          </div>

          <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-400 bg-green-900/20 border border-green-400/20 rounded-md p-3 text-center">
              A new verification link has been sent to your email address.
          </div>

          <form @submit.prevent="submit" class="space-y-4">
              <Button
                  type="submit"
                  class="w-full px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150"
                  :class="{ 'opacity-25': form.processing }" 
                  :disabled="form.processing"
              >
                  <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                  Resend verification email
              </Button>
          </form>

          <div class="mt-6 flex items-center justify-between text-sm">
              <Link
                  :href="route('profile.edit')"
                  class="text-gray-400 hover:text-red-400 underline"
              >
                  Edit Profile
              </Link>

              <TextLink
                  :href="route('logout')"
                  method="post"
                  as="button"
                  class="text-gray-400 hover:text-red-400 underline"
              >
                  Log Out
              </TextLink>
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
  </div>
</template>

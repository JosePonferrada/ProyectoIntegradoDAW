<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import TextLink from '@/components/TextLink.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false
});

const props = defineProps({
  title: {
    type: String,
    default: 'Login'
  }
});

const submit = () => {
  form.post(route('login'));
};
</script>

<template>
  <div>
    <Head :title="title"/>
    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-gradient-to-b from-gray-900 to-gray-800">
      <!-- Logo o imagen F1 -->
      <div class="flex justify-center w-full mb-8 sm:max-w-md">
        <div class="p-4 sm:p-6 bg-black bg-opacity-50 rounded-lg shadow-lg">
          <img 
  src="/img/logo-f1.svg" 
  alt="F1 Logo" 
  class="h-16 mx-auto dark:invert dark:brightness-200" 
/>
        </div>
      </div>

      <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border-t-4 border-red-600">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-white text-center mb-6">Acceso pilotos</h1>
        
        <form @submit.prevent="submit">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
            <input
              id="email"
              type="email"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-1"
              v-model="form.email"
              required
              autofocus
              autocomplete="username"
              placeholder="piloto@equipo.com"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <!-- Password -->
          <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
            <input
              id="password"
              type="password"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-1"
              v-model="form.password"
              required
              autocomplete="current-password"
              placeholder="••••••••"
            />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <!-- Remember me -->
          <div class="mt-4 flex items-center">
            <input 
              type="checkbox"
              id="remember_me"
              v-model="form.remember"
              name="remember"
              class="rounded border-gray-600 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50 bg-gray-700"
            />
            <label for="remember_me" class="ml-2 text-sm text-gray-300">Recordarme</label>
          </div>

          <div class="flex items-center justify-between mt-6">
            <Link :href="route('password.request')" class="text-sm text-gray-400 hover:text-red-400">
              ¿Olvidaste tu contraseña?
            </Link>

            <button
              type="submit"
              class="px-6 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring focus:ring-red-300 disabled:opacity-25 transition"
              :disabled="form.processing"
            >
              Iniciar sesión
            </button>
          </div>
          
          <div class="mt-8 text-center">
            <span class="text-gray-400">¿Aún no tienes una cuenta?</span>
            <Link :href="route('register')" class="ml-1 text-red-500 hover:text-red-400">
              Regístrate
            </Link>
          </div>
          
          <!-- Decoración temática F1 -->
          <div class="mt-10 pt-6 border-t border-gray-700 flex justify-center items-center">
            <div class="flex-shrink-0 text-white opacity-70">🏁</div>
            <div class="w-full h-2 mx-2 bg-gray-700 relative overflow-hidden rounded-full">
              <div class="absolute h-full w-1/2 bg-gradient-to-r from-red-600 via-red-500 to-red-600 animate-pulse"></div>
            </div>
            <div class="flex-shrink-0 text-white opacity-70">🏁</div>
          </div>
        </form>
      </div>
      
      <div class="mt-6 text-center text-sm text-gray-500">
        Formula 1 App &copy; {{ new Date().getFullYear() }}
      </div>
    </div>
  </div>
</template>
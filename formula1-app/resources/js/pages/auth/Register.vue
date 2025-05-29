<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';

// Definir el formulario con los campos adicionales
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  country_id: '',
  favorite_driver_id: '',
  favorite_constructor_id: '',
  avatar: null, // Añadir campo para el archivo
});

// Props para recibir datos
const props = defineProps({
  countries: Array,
  drivers: Array,
  constructors: Array,
});

// Manejar la selección del archivo
const handleFileChange = (event) => {
  form.avatar = event.target.files[0];
};

// Función para generar la URL de vista previa
const getAvatarPreview = (file) => {
  if (file && typeof file === 'object') {
    return window.URL.createObjectURL(file);
  }
  return '';
};

const submit = () => {
  form.post(route('register'), {
    forceFormData: true, // Asegurarse de que se envíe como FormData
    preserveScroll: true,
  });
};
</script>

<template>
  <div>
    <Head title="Register" />

    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-gradient-to-b from-gray-900 to-gray-800">
      <!-- Logo con fondo para que se vea bien en modo oscuro -->
      <div class="flex justify-center w-full mb-8 sm:max-w-md">
        <div class="p-4 sm:p-6">
          <img src="/img/logo-f1.png" alt="F1 Logo" class="h-40 mx-auto" />
        </div>
      </div>

      <div class="w-full sm:max-w-md px-6 py-8 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border-t-4 border-red-600">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-white text-center mb-6">Driver Registration</h1>
        
        <form @submit.prevent="submit" enctype="multipart/form-data">
          <!-- Nombre -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-300">Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
              required
              autofocus
              autocomplete="name"
            />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <!-- Email -->
          <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
              required
              autocomplete="username"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <!-- País -->
          <div class="mt-4">
            <label for="country" class="block text-sm font-medium text-gray-300">Country</label>
            <select
              id="country"
              v-model="form.country_id"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
            >
              <option value="" disabled selected>Select your country</option>
              <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                {{ country.name }}
              </option>
            </select>
            <InputError class="mt-2" :message="form.errors.country_id" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Piloto favorito -->
            <div>
              <label for="driver" class="block text-sm font-medium text-gray-300">Favorite Driver</label>
              <select
                id="driver"
                v-model="form.favorite_driver_id"
                class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
              >
                <option value="" disabled selected>Select</option>
                <option v-for="driver in drivers" :key="driver.driver_id" :value="driver.driver_id">
                  {{ driver.first_name }} {{ driver.last_name }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.favorite_driver_id" />
            </div>

            <!-- Constructor favorito -->
            <div>
              <label for="constructor" class="block text-sm font-medium text-gray-300">Favorite Team</label>
              <select
                id="constructor"
                v-model="form.favorite_constructor_id"
                class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
              >
                <option value="" disabled selected>Select</option>
                <option v-for="constructor in constructors" :key="constructor.constructor_id" :value="constructor.constructor_id">
                  {{ constructor.name }}
                </option>
              </select>
              <InputError class="mt-2" :message="form.errors.favorite_constructor_id" />
            </div>
          </div>

          <!-- Avatar -->
          <div class="mt-4">
            <label for="avatar" class="block text-sm font-medium text-gray-300">Avatar (Optional)</label>
            <div class="mt-1 flex items-center space-x-2">
              <div v-if="form.avatar" class="flex items-center">
                <img 
                  v-if="typeof form.avatar === 'object'"
                  :src="getAvatarPreview(form.avatar)" 
                  class="h-12 w-12 rounded-full object-cover border-2 border-gray-700" 
                  alt="Avatar Preview"
                />
                <span class="ml-2 text-sm text-gray-300">{{ form.avatar.name }}</span>
              </div>
              <label 
                class="relative cursor-pointer bg-gray-700 hover:bg-gray-600 py-2 px-4 rounded-md text-white text-sm"
              >
                <span>Select image</span>
                <input 
                  id="avatar" 
                  name="avatar" 
                  type="file" 
                  accept="image/*"
                  class="sr-only" 
                  @change="handleFileChange" 
                />
              </label>
              <button 
                v-if="form.avatar"
                type="button" 
                class="text-red-400 hover:text-red-500 text-sm"
                @click="form.avatar = null"
              >
                Remove
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-400">PNG, JPG or GIF (max. 2MB)</p>
            <InputError class="mt-2" :message="form.errors.avatar" />
          </div>

          <!-- Contraseña -->
          <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
              required
              autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password" />
          </div>

          <div class="mt-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirm Password</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="mt-1 block w-full bg-gray-700 border-gray-600 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-white p-2"
              required
              autocomplete="new-password"
            />
            <InputError class="mt-2" :message="form.errors.password_confirmation" />
          </div>

          <div class="flex items-center justify-between mt-6">
            <Link :href="route('login')" class="text-sm text-gray-400 hover:text-red-400">
              Already registered?
            </Link>

            <button
              type="submit"
              class="px-6 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring focus:ring-red-300 disabled:opacity-25 transition"
              :disabled="form.processing"
            >
              Register
            </button>
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
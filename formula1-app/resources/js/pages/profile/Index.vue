<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CountryFlag from '@/components/CountryFlag.vue';

const user = usePage().props.auth.user;
</script>

<template>
  <AppLayout title="Mi Perfil">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg dark:bg-gray-800 p-6">
          <h1 class="text-2xl font-bold mb-6 dark:text-white">Mi Perfil</h1>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Información del usuario -->
            <div class="space-y-4">
              <div>
                <h2 class="text-lg font-medium dark:text-gray-300">Información personal</h2>
                <div class="mt-2 grid grid-cols-1 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <div class="mt-1 text-gray-900 dark:text-white">{{ user.name }}</div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <div class="mt-1 text-gray-900 dark:text-white">{{ user.email }}</div>
                  </div>
                  <div class="mt-4">
                  <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Información Personal</h3>
                  <div class="mt-2 space-y-3">
                    <!-- País con bandera -->
                    <div v-if="user.country" class="flex items-center">
                      <span class="text-gray-600 dark:text-gray-400 w-24">País:</span>
                      <div class="flex items-center">
                        <CountryFlag 
                          :country-code="user.country.code" 
                          size="w-6 h-4" 
                          class="mr-2"
                        />
                        <span>{{ user.country.name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                </div>
              </div>
              
              <div>
                <h2 class="text-lg font-medium dark:text-gray-300">Preferencias F1</h2>
                <div class="mt-2 grid grid-cols-1 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Piloto favorito</label>
                    <div class="mt-1 text-gray-900 dark:text-white">
                      {{ user.favorite_driver ? `${user.favorite_driver.first_name} ${user.favorite_driver.last_name}` : 'No especificado' }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Constructor favorito</label>
                    <div class="mt-1 text-gray-900 dark:text-white">{{ user.favorite_constructor?.name || 'No especificado' }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex flex-col justify-start items-start space-y-4">
              <a href="#" class="px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Editar perfil
              </a>
              <a href="#" class="px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-100 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700">
                Cambiar contraseña
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
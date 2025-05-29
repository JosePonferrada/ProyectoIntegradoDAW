// filepath: resources/js/Pages/Formula1/LiveTimingWIP.vue
<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 p-4">
    <i class="fas fa-tools text-6xl text-yellow-500 mb-6"></i>
    <h1 class="text-4xl font-bold mb-4">Live Timing</h1>
    <p class="text-xl mb-2">This feature is currently under construction.</p>
    <p class="text-lg mb-8">Please check back later!</p>
    <button 
      @click="goBack"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150 ease-in-out"
    >
      Go Back
    </button>
  </div>
</template>

<script setup>
// No necesitamos Link de Inertia si vamos a usar el historial del navegador
// import { Link } from '@inertiajs/vue3'; 

const goBack = () => {
  window.history.back();
};
</script>

<style scoped>
/* Puedes añadir estilos específicos aquí si es necesario */
</style>
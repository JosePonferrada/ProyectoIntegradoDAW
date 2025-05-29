<template>
  <Head title="404 - Off Track" /> <!-- Opcional: Título para la pestaña -->
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 relative overflow-hidden p-6">
    
    <!-- Racing stripe decorations -->
    <div class="absolute top-0 left-0 right-0 h-5 md:h-6 bg-red-600 dark:bg-red-500"></div>
    <div class="absolute bottom-0 left-0 right-0 h-5 md:h-6 bg-red-600 dark:bg-red-500"></div>
    


    <main class="z-10 text-center max-w-lg w-full bg-white dark:bg-gray-800/80 backdrop-blur-sm p-8 sm:p-10 md:p-12 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700">
      
      <div class="mb-8">
        <TrafficCone class="h-20 w-20 sm:h-24 sm:w-24 md:h-28 md:w-28 text-red-500 dark:text-red-400 mx-auto animate-bounce" />
      </div>
      
      <div class="flex items-center justify-center text-4xl sm:text-5xl font-extrabold text-gray-800 dark:text-white mb-3 tracking-tight">
        <span>4</span>
        <Gauge class="w-10 h-10 sm:w-12 mx-1 text-red-500" />
        <span>4</span>
      </div>

      <h2 class="text-2xl sm:text-3xl font-semibold text-red-600 dark:text-red-500 mb-6">
        Detour Ahead!
      </h2>
      
      <p class="text-md sm:text-lg text-gray-600 dark:text-gray-300 mb-10 leading-relaxed">
        Looks like you've hit an unexpected roadblock. The page you were trying to reach isn't available.
      </p>
      
      <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 justify-center">
        <a 
          href="/" 
          class="inline-flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-150 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
        >
          <Home class="w-5 h-5 mr-2" />
          Return to Paddock
        </a>
        
        <button 
          @click="goBack" 
          class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-150 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
        >
          <ArrowLeft class="w-5 h-5 mr-2" />
          Previous Sector
        </button>
      </div>
    </main>
    
    <footer class="z-10 mt-10 text-center">
        <div class="flex justify-center items-center space-x-3">
            <TrafficCone class="h-6 w-6 text-gray-400 dark:text-gray-600 opacity-60 transform -rotate-12" /> 
            <TrafficCone class="h-8 w-8 text-gray-500 dark:text-gray-500 opacity-70" /> 
            <TrafficCone class="h-6 w-6 text-gray-400 dark:text-gray-600 opacity-60 transform rotate-12" />
        </div>
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            F1 Stats &copy; {{ new Date().getFullYear() }} - Always on the right track.
        </p>
    </footer>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { Home, ArrowLeft, TrafficCone, Gauge } from 'lucide-vue-next'; // Añadido Gauge

const goBack = () => {
  window.history.back();
};
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 relative overflow-hidden p-4">
    <!-- Racing stripe decorations -->
    <div class="absolute top-0 left-0 right-0 h-8 bg-f1-red"></div>
    <div class="absolute bottom-0 left-0 right-0 h-8 bg-f1-red"></div>
    
    <!-- Checkered flag pattern (top-right) -->
    <div class="absolute top-8 right-0 w-32 h-32 md:w-64 md:h-64 opacity-25">
      <div class="grid grid-cols-8 h-full w-full">
        <template v-for="i in 64" :key="i">
          <div :class="{ 'bg-black dark:bg-white': i % 2 === (Math.floor((i-1)/8) % 2 === 0 ? 0 : 1) }"></div>
        </template>
      </div>
    </div>
    
    <!-- Checkered flag pattern (bottom-left) -->
    <div class="absolute bottom-8 left-0 w-32 h-32 md:w-64 md:h-64 opacity-25">
      <div class="grid grid-cols-8 h-full w-full">
        <template v-for="i in 64" :key="i">
          <div :class="{ 'bg-black dark:bg-white': i % 2 === (Math.floor((i-1)/8) % 2 === 0 ? 0 : 1) }"></div>
        </template>
      </div>
    </div>
    
    <div class="z-10 text-center max-w-3xl bg-white dark:bg-gray-800 p-8 md:p-12 rounded-lg shadow-xl border-t-4 border-f1-red">
      <!-- Error code with F1 styling -->
      <div class="flex items-center justify-center mb-6">
        <span class="text-8xl md:text-9xl font-black text-f1-red tracking-tighter mr-4">4</span>
        <div class="relative">
          <div class="w-16 h-16 md:w-20 md:h-20 bg-f1-red rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 md:w-12 md:h-12 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 10l-9 3l9 3l-3 9l3 -9l3 9z" />
            </svg>
          </div>
        </div>
        <span class="text-8xl md:text-9xl font-black text-f1-red tracking-tighter ml-4">4</span>
      </div>
      
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-4">Pit Stop Problem!</h1>
      
      <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
        We've gone off track. The page you're looking for seems to have missed the apex or taken an unplanned pit stop.
      </p>
      
      <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 justify-center">
        <a href="/" class="px-6 py-3 bg-f1-red text-white font-medium rounded-md hover:bg-red-700 transition-colors flex items-center justify-center">
          <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Back to Grid
        </a>
        
        <button @click="goBack" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors flex items-center justify-center">
          <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Previous Sector
        </button>
      </div>
    </div>
    
    <!-- F1 car silhouette -->
    <div class="mt-12 opacity-20 dark:opacity-10">
      <svg xmlns="http://www.w3.org/2000/svg" width="240" height="80" viewBox="0 0 240 80" fill="currentColor" class="text-gray-800 dark:text-gray-200">
        <path d="M239.5,40c0,0-18.5-14.5-20-15.5s-11.5-8-22.5-8s-59.5,0-59.5,0l-10-9h-23l-6,5h-10l-9-5h-15l-8,5h-14l-7-5h-13l-4,5
        h-11l-6,14.5v8l6,8.5l13.5,12h38c0,0,4-4,12-4s12,4,12,4h38c0,0,4-4,12-4s12,4,12,4h65c0,0,10-1.5,15-3s10.5-3.5,13-5s6-4,6-4"/>
      </svg>
    </div>
  </div>
</template>

<script setup>
const goBack = () => {
  window.history.back();
};
</script>

<style scoped>
.bg-f1-red {
  background-color: #e10600;
}
.text-f1-red {
  color: #e10600;
}
</style>
<template>
  <div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center px-4 py-16">
    <!-- F1 Flag Pattern -->
    <div class="absolute top-0 w-full h-8 bg-gradient-to-r from-gray-900 via-f1-red to-gray-900"></div>
    
    <!-- Main Content -->
    <div class="max-w-xl w-full text-center">
      <!-- Status Code -->
      <div class="relative">
        <h1 class="text-9xl font-bold text-gray-800 dark:text-gray-700">403</h1>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full">
          <img 
            src="/images/f1-logo-red.svg" 
            alt="Formula 1" 
            class="h-16 mx-auto opacity-20"
            onerror="this.style.display='none'"
          />
        </div>
      </div>
      
      <!-- Title -->
      <h2 class="text-3xl md:text-4xl font-bold text-red-600 dark:text-red-500 mt-6 mb-2">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Forbidden
      </h2>
      
      <!-- Message -->
      <p class="mb-8 text-lg text-gray-200">
        You do not have permission to access this resource.
      </p>
      
      <!-- Divider -->
      <div class="flex items-center justify-center mb-8">
        <div class="w-16 h-1 bg-red-600 rounded-full"></div>
        <div class="mx-3">
          <i class="fas fa-flag-checkered text-xl text-gray-400"></i>
        </div>
        <div class="w-16 h-1 bg-red-600 rounded-full"></div>
      </div>
      
      <!-- Actions -->
      <div class="flex flex-col md:flex-row justify-center items-center gap-4">
        <a href="/" class="px-6 py-3 bg-f1-red hover:bg-red-700 text-white rounded-md transition-colors flex items-center">
          <i class="fas fa-home mr-2"></i>
          Back to Home
        </a>
        <a href="/login" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-md transition-colors flex items-center">
          <i class="fas fa-sign-in-alt mr-2"></i>
          Login
        </a>
      </div>
    </div>
    
    <!-- Racing stripe at bottom -->
    <div class="absolute bottom-0 w-full h-4 bg-red-600"></div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
</script>
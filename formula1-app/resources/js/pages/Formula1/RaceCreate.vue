<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Race</h1>
            <button 
              type="button" 
              @click="goBack"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
              Back to Races
            </button>
          </div>
          
          <div v-if="successMessage" class="mb-6 bg-green-50 dark:bg-green-900 p-4 rounded-md">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                  {{ successMessage }}
                </p>
              </div>
            </div>
          </div>
          
          <RaceForm 
            :errors="errors" 
            :processing="processing"
            @submit="createRace" 
            @cancel="goBack"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import RaceForm from '@/components/Formula1/RaceForm.vue';
import axios from 'axios';

const errors = ref({});
const processing = ref(false);
const successMessage = ref('');

function goBack() {
  router.visit('/races');
}

async function createRace(formData) {
  processing.value = true;
  errors.value = {};
  
  try {
    await axios.post('/api/races', formData);
    successMessage.value = 'Race created successfully!';
    setTimeout(() => {
      router.visit('/races');
    }, 2000);
  } catch (error) {
    processing.value = false;
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error creating race:', error);
      alert('An unexpected error occurred. Please try again.');
    }
  }
}
</script>
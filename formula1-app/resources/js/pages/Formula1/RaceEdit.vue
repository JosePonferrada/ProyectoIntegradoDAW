<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Race</h1>
            <button 
              type="button" 
              @click="goBack"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
              Back
            </button>
          </div>
          
          <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
            <span class="ml-3 text-gray-700 dark:text-gray-300">Loading race data...</span>
          </div>
          
          <div v-else-if="loadError" class="bg-red-50 dark:bg-red-900 p-4 rounded-md text-center">
            <p class="text-red-800 dark:text-red-200">{{ loadError }}</p>
            <button 
              @click="goBack"
              class="mt-3 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
              Return to Races
            </button>
          </div>
          
          <div v-else-if="successMessage" class="mb-6 bg-green-50 dark:bg-green-900 p-4 rounded-md">
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
            v-else
            :race="race" 
            :errors="errors" 
            :processing="processing"
            @submit="updateRace" 
            @cancel="goBack"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import RaceForm from '@/components/Formula1/RaceForm.vue';
import axios from 'axios';

const race = ref(null);
const errors = ref({});
const loading = ref(true);
const processing = ref(false);
const loadError = ref(null);
const successMessage = ref('');

const raceId = usePage().props.id;

onMounted(async () => {
  if (!raceId) {
    loadError.value = 'No race ID provided';
    loading.value = false;
    return;
  }
  
  try {
    const response = await axios.get(`/api/races/${raceId}`);
    race.value = response.data.data;
  } catch (error) {
    console.error('Error loading race:', error);
    loadError.value = 'Failed to load race details. Please try again.';
  } finally {
    loading.value = false;
  }
});

function goBack() {
  if (raceId) {
    router.visit(`/races/${raceId}`);
  } else {
    router.visit('/races');
  }
}

async function updateRace(formData) {
  processing.value = true;
  errors.value = {};
  
  try {
    await axios.put(`/api/races/${raceId}`, formData);
    successMessage.value = 'Race updated successfully!';
    setTimeout(() => {
      router.visit(`/races/${raceId}`);
    }, 2000);
  } catch (error) {
    processing.value = false;
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Error updating race:', error);
      alert('An unexpected error occurred while updating the race. Please try again.');
    }
  }
}
</script>
<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';

// Props
const props = defineProps({
  countries: Array
});

// Form setup
const form = useForm({
  first_name: '',
  last_name: '',
  code: '',
  number: null,
  country_id: '',
  date_of_birth: '',
  championships: 0,
  wins: 0,
  podiums: 0,
  poles: 0,
  active: true,
  biography: '',
  image: null
});

// File upload handling
const preview = ref(null);
const fileInput = ref(null);

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  
  form.image = file;
  
  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    preview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Submit form
const submit = () => {
  form.post('/api/drivers', {
    onSuccess: () => {
      router.visit('/drivers');
    }
  });
};
</script>

<template>
  <AppLayout>
    <Head title="Create Driver" />
    
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Driver</h1>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Información personal -->
              <div class="space-y-4">
                <div>
                  <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                  <input
                    id="first_name"
                    v-model="form.first_name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                    required
                  />
                  <div v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">{{ form.errors.first_name }}</div>
                </div>
                
                <div>
                  <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                  <input
                    id="last_name"
                    v-model="form.last_name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                    required
                  />
                  <div v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">{{ form.errors.last_name }}</div>
                </div>
                
                <div>
                  <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code (3 letters)</label>
                  <input
                    id="code"
                    v-model="form.code"
                    type="text"
                    maxlength="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white uppercase"
                  />
                  <div v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</div>
                </div>
                
                <div>
                  <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number</label>
                  <input
                    id="number"
                    v-model="form.number"
                    type="number"
                    min="0"
                    max="99"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                  />
                  <div v-if="form.errors.number" class="text-red-500 text-sm mt-1">{{ form.errors.number }}</div>
                </div>
                
                <div>
                  <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nationality</label>
                  <select
                    id="country"
                    v-model="form.country_id"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                    required
                  >
                    <option value="" disabled>Select a country</option>
                    <option 
                      v-for="country in countries" 
                      :key="country.country_id" 
                      :value="country.country_id"
                    >
                      {{ country.name }}
                    </option>
                  </select>
                  <div v-if="form.errors.country_id" class="text-red-500 text-sm mt-1">{{ form.errors.country_id }}</div>
                </div>
                
                <div>
                  <label for="date_of_birth" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                  <input
                    id="date_of_birth"
                    v-model="form.date_of_birth"
                    type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                    required
                  />
                  <div v-if="form.errors.date_of_birth" class="text-red-500 text-sm mt-1">{{ form.errors.date_of_birth }}</div>
                </div>
              </div>
              
              <!-- Estadísticas y foto -->
              <div class="space-y-4">
                <div>
                  <label for="championships" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Championships</label>
                  <input
                    id="championships"
                    v-model="form.championships"
                    type="number"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                  />
                  <div v-if="form.errors.championships" class="text-red-500 text-sm mt-1">{{ form.errors.championships }}</div>
                </div>
                
                <div>
                  <label for="wins" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Race Wins</label>
                  <input
                    id="wins"
                    v-model="form.wins"
                    type="number"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                  />
                  <div v-if="form.errors.wins" class="text-red-500 text-sm mt-1">{{ form.errors.wins }}</div>
                </div>
                
                <div>
                  <label for="podiums" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Podiums</label>
                  <input
                    id="podiums"
                    v-model="form.podiums"
                    type="number"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                  />
                  <div v-if="form.errors.podiums" class="text-red-500 text-sm mt-1">{{ form.errors.podiums }}</div>
                </div>
                
                <div>
                  <label for="poles" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pole Positions</label>
                  <input
                    id="poles"
                    v-model="form.poles"
                    type="number"
                    min="0"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                  />
                  <div v-if="form.errors.poles" class="text-red-500 text-sm mt-1">{{ form.errors.poles }}</div>
                </div>
                
                <div class="flex items-center">
                  <input
                    id="active"
                    v-model="form.active"
                    type="checkbox"
                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                  />
                  <label for="active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Driver is active</label>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver Image</label>
                  <div 
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-md"
                    :class="{'border-red-500': form.errors.image}"
                  >
                    <div class="space-y-1 text-center">
                      <div v-if="preview" class="mb-4">
                        <img :src="preview" alt="Preview" class="mx-auto h-32 w-auto" />
                      </div>
                      <div v-else class="flex text-sm text-gray-600 dark:text-gray-400">
                        <label
                          for="file-upload"
                          class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500"
                        >
                          <span>Upload a file</span>
                          <input 
                            id="file-upload" 
                            ref="fileInput"
                            type="file" 
                            class="sr-only"
                            @change="handleFileChange"
                            accept="image/*"
                          >
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        PNG, JPG, GIF up to 10MB
                      </p>
                    </div>
                  </div>
                  <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</div>
                </div>
              </div>
            </div>
            
            <!-- Biografía -->
            <div>
              <label for="biography" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Biography</label>
              <textarea
                id="biography"
                v-model="form.biography"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
              ></textarea>
              <div v-if="form.errors.biography" class="text-red-500 text-sm mt-1">{{ form.errors.biography }}</div>
            </div>
            
            <!-- Botones de acción -->
            <div class="flex justify-end space-x-3">
              <a 
                href="/drivers" 
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
              >
                Cancel
              </a>
              <button 
                type="submit" 
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Creating...' : 'Create Driver' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
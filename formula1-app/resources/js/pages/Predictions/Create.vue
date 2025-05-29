<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';

// Get user's saved timezone preference 
const userTimezone = ref(localStorage.getItem('preferredTimezone') || Intl.DateTimeFormat().resolvedOptions().timeZone);

function formatDate(dateString, timeString) {
  if (!dateString) return '';
  
  // Use timeString for the time portion if available
  let dateToUse = dateString;
  if (timeString) {
    // Extract just the time part
    const timeObj = new Date(timeString);
    const hours = timeObj.getHours();
    const minutes = timeObj.getMinutes();
    
    // Extract just the date part
    const dateObj = new Date(dateString);
    
    // Create a new Date with combined date and time
    dateObj.setHours(hours);
    dateObj.setMinutes(minutes);
    dateToUse = dateObj;
  } else {
    dateToUse = new Date(dateString);
  }
  
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
    timeZone: userTimezone.value
  };
  
  return new Intl.DateTimeFormat('en-US', options).format(dateToUse);
}

const props = defineProps({
  race: Object,
  drivers: Array,
  existingPrediction: Object
});

const selectedDrivers = ref([]);

// Load existing prediction data if available
if (props.existingPrediction) {
  // Sort positions by position number
  const sortedPositions = [...props.existingPrediction.positions].sort((a, b) => a.position - b.position);
  selectedDrivers.value = sortedPositions.map(pos => pos.driver_id);
}

const form = useForm({
  race_id: props.race.race_id, // Change from props.race.id to props.race.race_id
  positions: selectedDrivers.value.length ? selectedDrivers.value : Array(10).fill(null),
  dnf_count: props.existingPrediction?.dnf_count || 0,
  fastest_lap_driver_id: props.existingPrediction?.fastest_lap_driver_id || null,
});

const availableDrivers = computed(() => {
  return props.drivers.filter(driver => 
    !form.positions.includes(driver.driver_id) || 
    (props.selectedPosition !== null && form.positions[props.selectedPosition] === driver.driver_id)
  );
});

function getDriverName(driverId) {
  const driver = props.drivers.find(d => d.driver_id === driverId);
  return driver ? `${driver.first_name} ${driver.last_name}` : 'Select driver';
}

function submit() {
  form.post(route('predictions.store'));
}
</script>

<template>
  <AppLayout :title="`Prediction: ${race.name}`">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h1 class="text-2xl font-bold mb-4 dark:text-white">
            Prediction for {{ race?.name }}
          </h1>
          
          <div class="mb-6 text-gray-600 dark:text-gray-300">
            Date: {{ formatDate(race?.race_date, race?.start_time) }}
          </div>
          
          <form @submit.prevent="submit">
            <!-- Top 10 Section -->
            <div class="mb-6">
              <h2 class="text-xl font-bold mb-4 dark:text-white">Predict the Top 10</h2>
              
              <div v-for="position in 10" :key="position" class="mb-3 flex items-center">
                <span class="w-8 text-center font-bold dark:text-white">{{ position }}.</span>
                <select 
                  v-model="form.positions[position-1]" 
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600"
                >
                  <option :value="null">Select driver</option>
                  <option 
                    v-for="driver in props.drivers.filter(d => !form.positions.includes(d.driver_id) || form.positions[position-1] === d.driver_id)" 
                    :key="driver.driver_id" 
                    :value="driver.driver_id"
                  >
                    {{ driver.first_name }} {{ driver.last_name }}
                  </option>
                </select>
              </div>
              
              <div v-if="form.errors.positions" class="text-red-600 mt-1">
                {{ form.errors.positions }}
              </div>
            </div>
            
            <!-- DNFs and Fastest Lap -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-2">
                  How many drivers will not finish the race?
                </label>
                <div class="relative">
                  <select 
                    v-model="form.dnf_count"
                    class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:bg-gray-600"
                  >
                    <option v-for="n in 21" :key="n-1" :value="n-1">{{ n-1 }}</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-white">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                      <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                    </svg>
                  </div>
                </div>
                <div v-if="form.errors.dnf_count" class="text-red-600 mt-1">
                  {{ form.errors.dnf_count }}
                </div>
              </div>
              
              <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-2">
                  Who will set the fastest lap?
                </label>
                <select 
                  v-model="form.fastest_lap_driver_id" 
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-red-500 focus:border-red-500 rounded-md shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600"
                >
                  <option :value="null">Select driver</option>
                  <option 
                    v-for="driver in props.drivers" 
                    :key="driver.driver_id" 
                    :value="driver.driver_id"
                  >
                    {{ driver.first_name }} {{ driver.last_name }}
                  </option>
                </select>
                <div v-if="form.errors.fastest_lap_driver_id" class="text-red-600 mt-1">
                  {{ form.errors.fastest_lap_driver_id }}
                </div>
              </div>
            </div>
            
            <div class="flex justify-between">
              <Link :href="route('predictions.index')" 
                    class="inline-flex items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to predictions
              </Link>

              <button 
                type="submit" 
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                :disabled="form.processing"
              >
                {{ props.existingPrediction ? 'Update prediction' : 'Save prediction' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
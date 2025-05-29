<template>
  <div>
    <!-- Notificación -->
    <div 
      v-if="notification.show" 
      :class="[
        'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 transition-all duration-300',
        notification.type === 'success' ? 'bg-green-500' : 'bg-red-500',
        'text-white'
      ]"
    >
      <div class="flex items-center">
        <span>{{ notification.message }}</span>
        <button @click="notification.show = false" class="ml-3 text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Título con botón de edición -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white">Race Information</h2>
      <div class="flex space-x-2" v-if="isAdmin && race?.id">
        <a :href="`/formula1/races/${race.id}/edit`"
           class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-md font-medium transition-colors">
          <i class="fas fa-edit mr-1"></i> Edit Race
        </a>
        <button 
          @click="showDeleteModal = true" 
          class="px-3 py-1.5 bg-transparent border-2 border-red-600 text-red-600 dark:text-red-400 dark:border-red-400 hover:bg-red-600/10 text-sm rounded-md font-medium transition-colors">
          <i class="fas fa-trash-alt mr-1"></i> Delete
        </button>
      </div>
    </div>
    
    <!-- Modal de confirmación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirm Deletion</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to delete the <span class="font-semibold">{{ race?.name }}</span> race? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button @click="showDeleteModal = false" 
                  class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md text-sm font-medium">
            Cancel
          </button>
          <button @click="deleteRace" 
                  :disabled="deleting"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium">
            <span v-if="deleting">Deleting...</span>
            <span v-else>Delete Race</span>
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-f1-red mx-auto"></div>
      <p class="mt-2 text-gray-600 dark:text-gray-400">Loading race information...</p>
    </div>
    
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Schedule -->
      <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-md">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
            <i class="fas fa-calendar-alt mr-2 text-f1-red"></i>
            Schedule
          </h3>

          <CalendarButton v-if="race?.id" :race="race" :user-timezone="userTimezone" :format-date-time-fn="formatDateTimeFn" />
        </div>
        
        <!-- Weekend Format Badge -->
        <div class="mb-4">
          <span class="px-3 py-1 rounded-full text-xs font-medium" 
                :class="getWeekendFormatClass()">
            <i :class="[
              'mr-1',
              race?.weekend_format?.includes('sprint') ? 'fas fa-bolt' : 'fas fa-flag-checkered'
            ]"></i>
            {{ getWeekendFormatLabel() }}
          </span>
        </div>
        
        <ul class="space-y-2">
          <li v-if="race?.practice1_date" class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-300">Practice 1:</span>
            <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.practice1_date, race.practice1_time, props.userTimezone) }}</span>
          </li>
          
          <!-- Conditional rendering based on weekend format -->
          <template v-if="race?.weekend_format === 'sprint'">
            <!-- Sprint Format (Current: P1, SQ, Sprint, Q, Race) -->
            <li v-if="race?.sprint_qualifying_date" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Sprint Qualifying:</span>
              <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.sprint_qualifying_date, race.sprint_qualifying_time, props.userTimezone) }}</span>
            </li>
            <li v-if="race?.sprint_date" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Sprint:</span>
              <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.sprint_date, race.sprint_time, props.userTimezone) }}</span>
            </li>
            <li v-if="race?.qualifying_date" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Qualifying:</span>
              <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.qualifying_date, race.qualifying_time, props.userTimezone) }}</span>
            </li>
          </template>

          <template v-else>
            <!-- Traditional Format (P1, P2, P3, Q, Race) -->
            <li v-if="race?.practice2_date" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Practice 2:</span>
              <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.practice2_date, race.practice2_time, props.userTimezone) }}</span>
            </li>
            <li v-if="race?.practice3_date" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Practice 3:</span>
              <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.practice3_date, race.practice3_time, props.userTimezone) }}</span>
            </li>
            <li v-if="race?.qualifying_date" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Qualifying:</span>
              <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.qualifying_date, race.qualifying_time, props.userTimezone) }}</span>
            </li>
          </template>
          
          <!-- Race is always shown regardless of format -->
          <li v-if="race?.race_date" class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-300">Race:</span>
            <span class="text-gray-900 dark:text-white">{{ props.formatDateTimeFn(race.race_date, race.start_time, props.userTimezone) }}</span>
          </li>
        </ul>
      </div>
      
      <!-- Race Info -->
      <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-md">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          <i class="fas fa-info-circle mr-2 text-f1-red"></i>
          Details
        </h3>
        <ul class="space-y-2">
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-300">Round:</span>
            <span class="text-gray-900 dark:text-white">{{ race?.round || 'N/A' }}</span>
          </li>
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-300">Season:</span>
            <span class="text-gray-900 dark:text-white">{{ race?.season?.year || 'N/A' }}</span>
          </li>
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-300">Race Distance:</span>
            <span class="text-gray-900 dark:text-white">{{ race?.distance ? `${race.distance} km` : 'N/A' }}</span>
          </li>
          <li class="flex justify-between">
            <span class="text-gray-600 dark:text-gray-300">Laps:</span>
            <span class="text-gray-900 dark:text-white">{{ race?.scheduled_laps || 'N/A' }}</span>
          </li>

          <template v-if="race?.status?.toLowerCase() === 'completed'">
            <!-- Condiciones climáticas -->
            <li v-if="race?.weather_conditions" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Weather:</span>
              <span class="text-gray-900 dark:text-white flex items-center">
                <i :class="getWeatherIcon(race.weather_conditions)" class="mr-2 text-blue-500"></i>
                {{ race.weather_conditions }}
              </span>
            </li>
            
            <!-- Temperatura media -->
            <li v-if="race?.avg_temperature" class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-300">Temperature:</span>
              <span class="text-gray-900 dark:text-white flex items-center">
                <i class="fas fa-thermometer-half mr-2 text-red-500"></i>
                {{ race.avg_temperature }}° C
              </span>
            </li>
          </template>
        </ul>
      </div>
      
      <!-- Circuit Overview -->
      <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-md">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          <i class="fas fa-flag-checkered mr-2 text-f1-red"></i>
          Circuit Overview
        </h3>
        <div v-if="race?.circuit" class="flex flex-col sm:flex-row items-center">
          <img v-if="race.circuit.image" :src="`/storage/${race.circuit.image}`" 
               :alt="race.circuit.name" class="h-48 mb-4 sm:mb-0 sm:mr-6 rounded-md">
          <div>
            <h4 class="font-medium text-gray-900 dark:text-white">{{ race.circuit.name }}</h4>
            <p class="text-gray-600 dark:text-gray-300 mb-2">{{ race.circuit.location }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ race.circuit.description }}
            </p>
            <p v-if="race.circuit.length" class="text-sm text-gray-600 dark:text-gray-300 mt-2">
              Circuit Length: {{ race.circuit.length }} km
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import CalendarButton from './CalendarButton.vue';

const props = defineProps({
  race: Object,
  isAdmin: {
    type: Boolean,
    default: false
  },
  userTimezone: {
    type: String,
    required: true
  },
  formatDateTimeFn: {
    type: Function,
    required: true
  }
});

const emit = defineEmits(['raceDeleted']);
const loading = ref(false);
const showDeleteModal = ref(false);
const deleting = ref(false);
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});

// Función para mostrar notificaciones
const showNotification = (message, type = 'success') => {
  notification.value = {
    show: true,
    message,
    type
  };
  
  // Auto-cerrar después de 5 segundos
  setTimeout(() => {
    notification.value.show = false;
  }, 5000);
};

// Función para eliminar la carrera
async function deleteRace() {
  if (!props.race?.id) return;
  
  deleting.value = true;
  try {
    // Llamada a la API para eliminar la carrera
    await axios.delete(`/api/races/${props.race.id}`);
    
    // Cerrar el modal
    showDeleteModal.value = false;
    
    // Mostrar notificación de éxito
    showNotification('Race deleted successfully', 'success');
    
    // Emitir evento para informar al componente padre
    emit('raceDeleted');
    
    // Redirigir a la lista de carreras
    setTimeout(() => {
      router.visit('/races');
    }, 1500);
    
  } catch (error) {
    console.error('Error deleting race:', error);
    showNotification(error.response?.data?.message || 'Error deleting race', 'error');
  } finally {
    deleting.value = false;
  }
}

// Resto de tus funciones existentes...
function getWeekendFormatLabel() {
  if (!props.race?.weekend_format) return 'Traditional Weekend';
  
  switch(props.race.weekend_format) {
    case 'sprint':
      return 'Sprint Weekend';
    default:
      return 'Traditional Weekend';
  }
}

function getWeekendFormatClass() {
  if (!props.race?.weekend_format || props.race.weekend_format === 'traditional') {
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  }
  
  return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
}

function getWeatherIcon(conditions) {
  const conditionsLower = conditions.toLowerCase();
  
  if (conditionsLower.includes('clear') || conditionsLower.includes('sunny')) {
    return 'fas fa-sun text-yellow-500';
  } else if (conditionsLower.includes('cloudy') || conditionsLower.includes('nublado')) {
    if (conditionsLower.includes('partly cloudy') || conditionsLower.includes('parcialmente nublado')) {
      return 'fas fa-cloud-sun text-gray-400';
    }
    return 'fas fa-cloud text-gray-400';
  } else if (conditionsLower.includes('rain') || conditionsLower.includes('rainy') || conditionsLower.includes('lloviendo')) {
    return 'fas fa-cloud-rain text-blue-400';
  } else if (conditionsLower.includes('storm') || conditionsLower.includes('thunderstorm')) {
    return 'fas fa-bolt text-yellow-400';
  } else {
    return 'fas fa-cloud text-gray-400'; // Icono por defecto
  }
}

function confirmDelete() {
  showDeleteModal.value = true;
}

onMounted(() => {
  console.log('RaceInfo Race data:', props.race);
  
  // Probar el formateo directamente usando la función de props
  if (props.race?.race_date && props.formatDateTimeFn) {
    console.log('RaceInfo Formatted date test (from props):', props.formatDateTimeFn(props.race.race_date, props.race.start_time, props.userTimezone));
  }
});
</script>
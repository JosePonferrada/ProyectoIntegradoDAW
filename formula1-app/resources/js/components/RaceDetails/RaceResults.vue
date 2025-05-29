<template>
  <div>
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-f1-red mx-auto"></div>
      <p class="mt-2 text-gray-600 dark:text-gray-400">Loading race results...</p>
    </div>
    
    <div v-else-if="!results || !results.length" class="text-center py-8">
      <p class="text-gray-600 dark:text-gray-400">No results available yet.</p>
    </div>
    
    <div v-else class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 shadow">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-100 dark:bg-gray-800/50">
            <tr>
              <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-16">Pos</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-16">No</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Driver</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Car</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Laps</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Time/Status</th>
              <th scope="col" class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pts</th>
              <!-- <th v-if="isAdmin && resultsMode === 'view'" scope="col" class="relative px-4 py-3"><span class="sr-only">Actions</span></th> -->
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
            <tr v-for="result in results" :key="result.result_id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="whitespace-nowrap px-3 py-4 text-sm font-medium" :class="getPositionClass(result.position_text || result.position)">
                {{ result.position_text || result.position }}
                <i 
                  v-if="result.id === fastestLapData.fastestResultId" 
                  class="fas fa-stopwatch text-xs text-purple-500 ml-1.5" 
                  title="Fastest Lap"
                ></i>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ result.driver?. number || '-' }}
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center">
                  <img 
                    v-if="result.driver?.image" 
                    :src="`/storage/${result.driver.image}`" 
                    :alt="`${result.driver.first_name} ${result.driver.last_name}`" 
                    class="h-10 w-10 rounded-full mr-3 object-cover flex-shrink-0"
                  >
                  <div v-else class="h-10 w-10 rounded-full mr-3 bg-gray-300 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-xl text-gray-500 dark:text-gray-400"></i>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ result.driver?.first_name }} {{ result.driver?.last_name }}
                    </div>
                    <div v-if="result.driver?.nationality" class="text-xs text-gray-500 dark:text-gray-400 flex items-center mt-0.5">
                       <CountryFlag :countryCode="result.driver.nationality.code" class="h-3 w-5 mr-1.5" />
                    </div>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                {{ result.constructor?.name || '-' }}
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ result.laps }}
              </td>
              <td class="whitespace-nowrap px-4 py-4 text-sm">
                <span v-if="result.status === 'Finished' && result.race_time" class="text-gray-700 dark:text-gray-300">{{ result.race_time }}</span>
                <span v-else-if="result.status === 'Finished' && !result.race_time" class="text-gray-700 dark:text-gray-300">-</span>
                <span v-else class="font-semibold" :class="getStatusClass(result.status)">{{ result.status }}</span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ result.points != null ? parseFloat(result.points).toFixed(0) : '-' }}
              </td>
              <!-- <td v-if="isAdmin && resultsMode === 'view'" class="whitespace-nowrap px-4 py-4 text-right text-sm font-medium">
                <!-- Botones de Edit/Delete
              </td> -->
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal para Añadir/Editar Resultados -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Fondo oscuro -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>

        <!-- Centro del modal -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                  {{ editingResult ? 'Edit Race Result' : 'Add Race Result' }}
                </h3>
                <div class="mt-4 space-y-4">
                  <!-- Formulario para añadir/editar resultado -->
                  <form @submit.prevent="saveResult">
                    <!-- Campos del formulario aquí -->
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                      <!-- Driver selector -->
                      <div class="sm:col-span-2">
                        <label for="driver" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver</label>
                        <select 
                          id="driver" 
                          v-model="form.driver_id" 
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                          required
                        >
                          <option value="" disabled>Select Driver</option>
                          <option v-for="driver in availableDrivers" :key="driver.driver_id" :value="driver.driver_id">
                            {{ driver.first_name }} {{ driver.last_name }}
                          </option>
                        </select>
                        <p v-if="errors.driver_id" class="mt-2 text-sm text-red-600">{{ errors.driver_id }}</p>
                      </div>

                      <!-- Constructor selector -->
                      <div class="sm:col-span-2">
                        <label for="constructor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Constructor</label>
                        <select 
                          id="constructor" 
                          v-model="form.constructor_id" 
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                          required
                        >
                          <option value="" disabled>Select Constructor</option>
                          <option v-for="constructor in availableConstructors" :key="constructor.constructor_id" :value="constructor.constructor_id">
                            {{ constructor.name }}
                          </option>
                        </select>
                        <p v-if="errors.constructor_id" class="mt-2 text-sm text-red-600">{{ errors.constructor_id }}</p>
                      </div>

                      <!-- Position -->
                      <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Position</label>
                        <input 
                          type="number" 
                          id="position" 
                          v-model="form.position" 
                          min="1"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                        >
                        <p v-if="errors.position" class="mt-2 text-sm text-red-600">{{ errors.position }}</p>
                      </div>

                      <!-- Position Text (para DNF, DSQ, etc. si es necesario anular la posición numérica) -->
                      <div>
                        <label for="position_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Position Text (e.g., DNF, NC)</label>
                        <input 
                          type="text" 
                          id="position_text" 
                          v-model="form.position_text" 
                          placeholder="Overrides position number"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                        >
                        <p v-if="errors.position_text" class="mt-2 text-sm text-red-600">{{ errors.position_text }}</p>
                      </div>
                      
                      <!-- Grid Position -->
                      <div>
                        <label for="grid_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grid Position</label>
                        <input 
                          type="number" 
                          id="grid_position" 
                          v-model="form.grid_position" 
                          min="1"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                        >
                        <p v-if="errors.grid_position" class="mt-2 text-sm text-red-600">{{ errors.grid_position }}</p>
                      </div>

                      <!-- Laps -->
                      <div>
                        <label for="laps" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Laps</label>
                        <input 
                          type="number" 
                          id="laps" 
                          v-model="form.laps" 
                          min="0"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                        >
                        <p v-if="errors.laps" class="mt-2 text-sm text-red-600">{{ errors.laps }}</p>
                      </div>

                      <!-- Points -->
                      <div>
                        <label for="points" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Points</label>
                        <input 
                          type="number" 
                          id="points" 
                          v-model="form.points" 
                          min="0"
                          step="0.01"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                          required
                        >
                        <p v-if="errors.points" class="mt-2 text-sm text-red-600">{{ errors.points }}</p>
                      </div>

                      <!-- Status -->
                      <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select 
                          id="status" 
                          v-model="form.status" 
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                        >
                          <option value="Finished">Finished</option>
                          <option value="DNF">DNF</option>
                          <option value="DSQ">Disqualified</option>
                          <option value="DNS">Did Not Start</option>
                          <option value="Retired">Retired</option>
                          <option value="Accident">Accident</option>
                          <option value="Mechanical">Mechanical</option>
                          <option value="Power Unit">Power Unit</option>
                        </select>
                        <p v-if="errors.status" class="mt-2 text-sm text-red-600">{{ errors.status }}</p>
                      </div>

                      <!-- Race Time (solo para finalizados) -->
                      <div v-if="form.status === 'Finished'">
                        <label for="race_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Race Time</label>
                        <input 
                          type="text" 
                          id="race_time" 
                          v-model="form.race_time" 
                          placeholder="e.g. 1:30:45.123"
                          class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                        >
                        <p v-if="errors.race_time" class="mt-2 text-sm text-red-600">{{ errors.race_time }}</p>
                      </div>

                      <!-- Fastest Lap -->
                      <div class="sm:col-span-2">
                        <div class="flex items-center">
                          <input 
                            type="checkbox" 
                            id="fastest_lap_check" 
                            v-model="form.has_fastest_lap"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                          >
                          <label for="fastest_lap_check" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Fastest Lap
                          </label>
                        </div>
                      </div>

                      <!-- Fastest Lap Info (solo si tiene vuelta rápida) -->
                      <div v-if="form.has_fastest_lap" class="sm:col-span-2 grid grid-cols-2 gap-4">
                        <div>
                          <label for="fastest_lap" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fastest Lap Number</label>
                          <input 
                            type="number" 
                            id="fastest_lap" 
                            v-model="form.fastest_lap" 
                            min="1"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                          >
                        </div>
                        <div>
                          <label for="fastest_lap_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fastest Lap Time</label>
                          <input 
                            type="text" 
                            id="fastest_lap_time" 
                            v-model="form.fastest_lap_time" 
                            placeholder="e.g. 1:32.456"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                          >
                        </div>
                        <div class="col-span-2">
                          <label for="fastest_lap_speed" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fastest Lap Speed (km/h)</label>
                          <input 
                            type="number" 
                            id="fastest_lap_speed" 
                            v-model="form.fastest_lap_speed" 
                            step="0.01"
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white dark:bg-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:text-white dark:border-gray-600"
                          >
                        </div>
                      </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                      <button 
                        type="submit" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                        :disabled="isLoading"
                      >
                        <span v-if="isLoading" class="mr-2">
                          <i class="fas fa-spinner fa-spin"></i>
                        </span>
                        {{ editingResult ? 'Update' : 'Add' }}
                      </button>
                      <button 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                        @click="closeModal"
                      >
                        Cancel
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                  Delete Race Result
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to delete this result? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button 
              type="button" 
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              :disabled="isDeleting"
              @click="deleteResult"
            >
              <span v-if="isDeleting" class="mr-2">
                <i class="fas fa-spinner fa-spin"></i>
              </span>
              Delete
            </button>
            <button 
              type="button" 
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              @click="showDeleteModal = false"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notificación de éxito/error -->
    <div 
      v-if="notification.show" 
      :class="[
        'fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-500',
        notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      ]"
    >
      <div class="flex items-center">
        <i :class="notification.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" class="mr-2"></i>
        <span>{{ notification.message }}</span>
        <button @click="notification.show = false" class="ml-4 text-white hover:text-gray-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CountryFlag from '@/components/CountryFlag.vue';
import axios from 'axios';

const props = defineProps({
  race: Object,
  results: Array,
  loading: Boolean,
  resultsMode: {
    type: String,
    default: 'view'
  }
});

const emit = defineEmits(['results-updated']);

// Estado para modal y formulario
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingResult = ref(null);
const selectedResult = ref(null);
const isLoading = ref(false);
const isDeleting = ref(false);
const errors = ref({});
const availableDrivers = ref([]);
const availableConstructors = ref([]);
const notification = ref({
  show: false,
  message: '',
  type: 'success'
});

// Comprobar si el usuario es admin
const isAdmin = computed(() => {
  const user = usePage().props.auth.user;
  return user && user.role === 'admin';
});

// Formulario para añadir/editar resultado
const form = ref({
  result_id: null,
  race_id: props.race?.race_id,
  driver_id: '',
  constructor_id: '',
  position: null,
  position_text: '',
  position_order: null,
  grid_position: null,
  laps: null,
  points: 0,
  status: 'Finished',
  race_time: '',
  has_fastest_lap: false,
  fastest_lap: null,
  fastest_lap_time: '',
  fastest_lap_speed: null
});

// --- INICIO LÓGICA PARA VUELTA RÁPIDA ---
function parseTimeToMilliseconds(timeStr) {
  if (!timeStr || typeof timeStr !== 'string') return Infinity;
  
  // Regex mejorada para HH:MM:SS.mmm o MM:SS.mmm
  // Grupo 1: Horas (opcional)
  // Grupo 2: Minutos
  // Grupo 3: Segundos
  // Grupo 4: Milisegundos
  const parts = timeStr.match(/^(?:(\d{1,2}):)?([0-5]?\d):([0-5]?\d)\.(\d{3})$/);
  
  if (!parts) {
    // console.warn(`Invalid time format for fastest lap: ${timeStr}`); // Opcional: para debugging
    return Infinity; // Formato inválido
  }

  const hours = parts[1] ? parseInt(parts[1], 10) : 0; // Horas son opcionales, default a 0
  const minutes = parseInt(parts[2], 10);
  const seconds = parseInt(parts[3], 10);
  const milliseconds = parseInt(parts[4], 10);

  if (isNaN(hours) || isNaN(minutes) || isNaN(seconds) || isNaN(milliseconds)) {
    // console.warn(`Could not parse time components for: ${timeStr}`); // Opcional: para debugging
    return Infinity;
  }
  
  return (hours * 60 * 60 * 1000) + (minutes * 60 * 1000) + (seconds * 1000) + milliseconds;
}

const fastestLapData = computed(() => {
  if (!props.results || props.results.length === 0) {
    return { fastestResultId: null, bestTimeMs: Infinity };
  }

  let bestTimeMs = Infinity;
  let fastestResultId = null; 

  props.results.forEach(result => {
    if (result.fastest_lap && Number(result.fastest_lap) > 0 && result.fastest_lap_time) {
      const currentTimeMs = parseTimeToMilliseconds(result.fastest_lap_time);
      if (currentTimeMs < bestTimeMs) {
        bestTimeMs = currentTimeMs;
        fastestResultId = result.id; // CAMBIADO DE result.result_id a result.id
      }
    }
  });
  console.log('Fastest Lap Data (inside computed):', { fastestResultId, bestTimeMs }); 
  return { fastestResultId, bestTimeMs };
});
// --- FIN LÓGICA PARA VUELTA RÁPIDA ---

// Determinar el color para cada constructor
function constructorColor(constructor) {
  if (!constructor) return '#cccccc';
  
  const name = constructor.name || '';
  
  if (name.includes('Mercedes')) return '#00D2BE';
  if (name.includes('Ferrari')) return '#DC0000';
  if (name.includes('Red Bull')) return '#0600EF';
  if (name.includes('McLaren')) return '#FF8700';
  if (name.includes('Alpine')) return '#0090FF';
  if (name.includes('Racing Bulls')) return '#2B4562';
  if (name.includes('Aston Martin')) return '#006F62';
  if (name.includes('Williams')) return '#005AFF';
  if (name.includes('Kick Sauber') || name.includes('Sauber')) return '#52E252';
  if (name.includes('Haas')) return '#B6BABD';
  
  return '#cccccc';
}

// Obtener clase para posiciones especiales
function getPositionClass(position) {
  const posNum = parseInt(position, 10);
  if (isNaN(posNum)) return 'text-gray-900 dark:text-white';

  if (posNum === 1) {
    return 'font-bold text-yellow-500 dark:text-yellow-400';
  } else if (posNum === 2) {
    return 'font-bold text-gray-500 dark:text-gray-400';
  } else if (posNum === 3) {
    return 'font-bold text-orange-600 dark:text-orange-500';
  }
  return 'text-gray-900 dark:text-white';
}

// Obtener clase para el estado
function getStatusClass(status) {
  if (status === 'DNF' || status === 'Retired') {
    return 'text-red-600 dark:text-red-400';
  } else if (status === 'DSQ') {
    return 'text-yellow-600 dark:text-yellow-400';
  }
  return 'text-gray-700 dark:text-gray-300';
}

// Cargar datos necesarios para el formulario
async function loadFormData() {
  try {
    if (availableDrivers.value.length === 0) {
      const driversResponse = await axios.get('/api/drivers');
      availableDrivers.value = driversResponse.data.data || [];
    }
    if (availableConstructors.value.length === 0) {
      const constructorsResponse = await axios.get('/api/constructors');
      availableConstructors.value = constructorsResponse.data.data || [];
    }
  } catch (error) {
    console.error('Error loading form data:', error);
    showNotification('Failed to load required data for the form.', 'error');
  }
}

// Abrir modal para añadir nuevo resultado
function openAddModal() {
  editingResult.value = null;
  form.value = {
    result_id: null,
    race_id: props.race?.race_id,
    driver_id: '',
    constructor_id: '',
    position: null,
    position_text: '',
    position_order: null,
    grid_position: null,
    laps: null,
    points: 0,
    status: 'Finished',
    race_time: '',
    has_fastest_lap: false,
    fastest_lap: null,
    fastest_lap_time: '',
    fastest_lap_speed: null
  };
  errors.value = {};
  loadFormData();
  showModal.value = true;
}

// Abrir modal para editar resultado existente
function editResult(result) {
  editingResult.value = result;
  form.value = {
    result_id: result.result_id,
    race_id: result.race_id,
    driver_id: result.driver_id,
    constructor_id: result.constructor_id,
    position: result.position,
    position_text: result.position_text || '',
    position_order: result.position_order,
    grid_position: result.grid_position,
    laps: result.laps,
    points: result.points,
    status: result.status || 'Finished',
    race_time: result.race_time || '',
    has_fastest_lap: !!result.fastest_lap_time,
    fastest_lap: result.fastest_lap,
    fastest_lap_time: result.fastest_lap_time || '',
    fastest_lap_speed: result.fastest_lap_speed
  };
  errors.value = {};
  loadFormData();
  showModal.value = true;
}

// Cerrar modal
function closeModal() {
  showModal.value = false;
  setTimeout(() => {
    editingResult.value = null;
    errors.value = {};
  }, 300);
}

// Guardar resultado
async function saveResult() {
  isLoading.value = true;
  errors.value = {};
  
  const dataToSave = { ...form.value };

  if (!dataToSave.has_fastest_lap) {
    dataToSave.fastest_lap = null;
    dataToSave.fastest_lap_time = null;
    dataToSave.fastest_lap_speed = null;
  }
  delete dataToSave.has_fastest_lap;

  const isEditing = !!editingResult.value;
  const url = isEditing ? `/api/race-results/${editingResult.value.result_id}` : '/api/race-results';
  const method = isEditing ? 'put' : 'post';

  try {
    await axios[method](url, dataToSave);
    showNotification(`Race result ${isEditing ? 'updated' : 'added'} successfully.`, 'success');
    emit('results-updated');
    closeModal();
  } catch (error) {
    console.error('Error saving race result:', error);
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors;
      showNotification('Please check the form for errors.', 'error');
    } else {
      showNotification(`Failed to ${isEditing ? 'update' : 'add'} race result. ` + (error.response?.data?.message || error.message), 'error');
    }
  } finally {
    isLoading.value = false;
  }
}

// Confirmar eliminación de resultado
function confirmDelete(result) {
  selectedResult.value = result;
  showDeleteModal.value = true;
}

// Eliminar resultado
async function deleteResult() {
  if (!selectedResult.value) return;
  
  isDeleting.value = true;
  
  try {
    await axios.delete(`/api/race-results/${selectedResult.value.result_id}`);
    showNotification('Race result deleted successfully.', 'success');
    emit('results-updated');
    showDeleteModal.value = false;
  } catch (error) {
    console.error('Error deleting race result:', error);
    showNotification('Failed to delete race result. ' + (error.response?.data?.message || error.message), 'error');
  } finally {
    isDeleting.value = false;
    selectedResult.value = null;
  }
}

// Mostrar notificación
function showNotification(message, type = 'success') {
  notification.value = {
    show: true,
    message,
    type
  };
  
  setTimeout(() => {
    notification.value.show = false;
  }, 5000);
}

onMounted(() => {
});
</script>

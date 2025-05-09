<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <!-- Información básica de la carrera -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nombre de la carrera -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Race Name</label>
        <input 
          id="name" 
          v-model="form.name" 
          type="text" 
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          required
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
      </div>
      
      <!-- Selector de circuito -->
      <div>
        <label for="circuit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Circuit</label>
        <select
          id="circuit_id"
          v-model="form.circuit_id"
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          required
        >
          <option value="" disabled>Select a circuit</option>
          <option v-for="circuit in circuits" :key="circuit.id" :value="circuit.id">
            {{ circuit.name }}, {{ circuit.country?.name }}
          </option>
        </select>
        <p v-if="errors.circuit_id" class="mt-1 text-sm text-red-600">{{ errors.circuit_id[0] }}</p>
      </div>
      
      <!-- Selector de temporada -->
      <div>
        <label for="season_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Season</label>
        <select
          id="season_id"
          v-model="form.season_id"
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          required
        >
          <option value="" disabled>Select a season</option>
          <option v-for="season in seasons" :key="season.id" :value="season.id">
            {{ season.year }}
          </option>
        </select>
        <p v-if="errors.season_id" class="mt-1 text-sm text-red-600">{{ errors.season_id[0] }}</p>
      </div>
      
      <!-- Número de ronda -->
      <div>
        <label for="round" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Round</label>
        <input 
          id="round" 
          v-model="form.round" 
          type="number" 
          min="1" 
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          required
        />
        <p v-if="errors.round" class="mt-1 text-sm text-red-600">{{ errors.round[0] }}</p>
      </div>
    </div>
    
    <!-- Sección de fechas y horarios -->
    <div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Race Weekend Schedule</h3>
      
      <!-- Formato del fin de semana -->
      <div class="mb-6">
        <label for="weekend_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Weekend Format</label>
        <select
          id="weekend_format"
          v-model="form.weekend_format"
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
        >
          <option value="traditional">Traditional Format</option>
          <option value="sprint">Sprint Format</option>
        </select>
        <p v-if="errors.weekend_format" class="mt-1 text-sm text-red-600">{{ errors.weekend_format[0] }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Práctica 1 -->
        <div>
          <label for="practice1_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice 1 Date</label>
          <input 
            id="practice1_date" 
            v-model="form.practice1_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.practice1_date" class="mt-1 text-sm text-red-600">{{ errors.practice1_date[0] }}</p>
        </div>
        
        <div>
          <label for="practice1_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice 1 Time</label>
          <input 
            id="practice1_time" 
            v-model="form.practice1_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.practice1_time" class="mt-1 text-sm text-red-600">{{ errors.practice1_time[0] }}</p>
        </div>
        
        <!-- Práctica 2 -->
        <div  v-if="form.weekend_format !== 'sprint'">
          <label for="practice2_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice 2 Date</label>
          <input 
            id="practice2_date" 
            v-model="form.practice2_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.practice2_date" class="mt-1 text-sm text-red-600">{{ errors.practice2_date[0] }}</p>
        </div>
        
        <div  v-if="form.weekend_format !== 'sprint'">
          <label for="practice2_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice 2 Time</label>
          <input 
            id="practice2_time" 
            v-model="form.practice2_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.practice2_time" class="mt-1 text-sm text-red-600">{{ errors.practice2_time[0] }}</p>
        </div>
        
        <!-- Práctica 3 (solo formato tradicional) -->
        <div v-if="form.weekend_format !== 'sprint'">
          <label for="practice3_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice 3 Date</label>
          <input 
            id="practice3_date" 
            v-model="form.practice3_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.practice3_date" class="mt-1 text-sm text-red-600">{{ errors.practice3_date[0] }}</p>
        </div>
        
        <div v-if="form.weekend_format !== 'sprint'">
          <label for="practice3_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice 3 Time</label>
          <input 
            id="practice3_time" 
            v-model="form.practice3_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.practice3_time" class="mt-1 text-sm text-red-600">{{ errors.practice3_time[0] }}</p>
        </div>
        
        <!-- Sprint Qualifying (solo formato sprint) -->
        <div v-if="form.weekend_format === 'sprint'">
          <label for="sprint_qualifying_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sprint Qualifying Date</label>
          <input 
            id="sprint_qualifying_date" 
            v-model="form.sprint_qualifying_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.sprint_qualifying_date" class="mt-1 text-sm text-red-600">{{ errors.sprint_qualifying_date[0] }}</p>
        </div>
        
        <div v-if="form.weekend_format === 'sprint'">
          <label for="sprint_qualifying_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sprint Qualifying Time</label>
          <input 
            id="sprint_qualifying_time" 
            v-model="form.sprint_qualifying_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.sprint_qualifying_time" class="mt-1 text-sm text-red-600">{{ errors.sprint_qualifying_time[0] }}</p>
        </div>
        
        <!-- Sprint Race (solo formato sprint) -->
        <div v-if="form.weekend_format === 'sprint'">
          <label for="sprint_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sprint Race Date</label>
          <input 
            id="sprint_date" 
            v-model="form.sprint_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.sprint_date" class="mt-1 text-sm text-red-600">{{ errors.sprint_date[0] }}</p>
        </div>
        
        <div v-if="form.weekend_format === 'sprint'">
          <label for="sprint_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sprint Race Time</label>
          <input 
            id="sprint_time" 
            v-model="form.sprint_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.sprint_time" class="mt-1 text-sm text-red-600">{{ errors.sprint_time[0] }}</p>
        </div>
        
        <!-- Clasificación -->
        <div>
          <label for="qualifying_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qualifying Date</label>
          <input 
            id="qualifying_date" 
            v-model="form.qualifying_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.qualifying_date" class="mt-1 text-sm text-red-600">{{ errors.qualifying_date[0] }}</p>
        </div>
        
        <div>
          <label for="qualifying_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qualifying Time</label>
          <input 
            id="qualifying_time" 
            v-model="form.qualifying_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
          />
          <p v-if="errors.qualifying_time" class="mt-1 text-sm text-red-600">{{ errors.qualifying_time[0] }}</p>
        </div>
        
        <!-- Carrera -->
        <div>
          <label for="race_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Race Date</label>
          <input 
            id="race_date" 
            v-model="form.race_date" 
            type="date" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
            required
          />
          <p v-if="errors.race_date" class="mt-1 text-sm text-red-600">{{ errors.race_date[0] }}</p>
        </div>
        
        <div>
          <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Race Start Time</label>
          <input 
            id="start_time" 
            v-model="form.start_time" 
            type="time" 
            class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
            required
          />
          <p v-if="errors.start_time" class="mt-1 text-sm text-red-600">{{ errors.start_time[0] }}</p>
        </div>
      </div>
    </div>
    
    <!-- Detalles adicionales de la carrera -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Vueltas programadas -->
      <div>
        <label for="scheduled_laps" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scheduled Laps</label>
        <input 
          id="scheduled_laps" 
          v-model="form.scheduled_laps" 
          type="number" 
          min="1" 
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
        />
        <p v-if="errors.scheduled_laps" class="mt-1 text-sm text-red-600">{{ errors.scheduled_laps[0] }}</p>
      </div>
      
      <!-- Distancia de carrera -->
      <div>
        <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Race Distance (km)</label>
        <input 
          id="distance" 
          v-model="form.distance" 
          type="number" 
          min="0" 
          step="0.001" 
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
        />
        <p v-if="errors.distance" class="mt-1 text-sm text-red-600">{{ errors.distance[0] }}</p>
      </div>
      
      <!-- Estado -->
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
          id="status"
          v-model="form.status"
          class="w-full px-4 py-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-f1-red focus:border-transparent shadow-sm"
        >
          <option value="Scheduled">Scheduled</option>
          <option value="Confirmed">Confirmed</option>
          <option value="Ongoing">Ongoing</option>
          <option value="Completed">Completed</option>
          <option value="Postponed">Postponed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
        <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</p>
      </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="flex justify-end space-x-3">
      <button
        type="button"
        @click="$emit('cancel')"
        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="processing"
        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
      >
        {{ race ? 'Update Race' : 'Create Race' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted, watchEffect } from 'vue';
import axios from 'axios';

const props = defineProps({
  race: {
    type: Object,
    default: null
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  processing: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel']);

const circuits = ref([]);
const seasons = ref([]);
const form = ref({
  name: '',
  circuit_id: '',
  season_id: '',
  round: '',
  race_date: '',
  start_time: '',
  practice1_date: '',
  practice1_time: '',
  practice2_date: '',
  practice2_time: '',
  practice3_date: '',
  practice3_time: '',
  qualifying_date: '',
  qualifying_time: '',
  sprint_qualifying_date: '',
  sprint_qualifying_time: '',
  sprint_date: '',
  sprint_time: '',
  scheduled_laps: '',
  distance: '',
  status: 'Scheduled',
  weekend_format: 'traditional'
});

// Cargar datos iniciales
onMounted(async () => {
  try {
    const [circuitsResponse, seasonsResponse] = await Promise.all([
      axios.get('/api/circuits'),
      axios.get('/api/seasons')
    ]);
    
    circuits.value = circuitsResponse.data.data || [];
    seasons.value = seasonsResponse.data.data || [];
    
    // Si estamos editando, rellenar el formulario
    if (props.race) {
      populateForm();
    }
  } catch (error) {
    console.error('Error loading form data:', error);
  }
});

// Actualizar formulario cuando cambia la prop race
watchEffect(() => {
  if (props.race) {
    populateForm();
  }
});

// Función mejorada para rellenar el formulario
function populateForm() {
  if (!props.race) return;

  // Obtenemos el objeto race correcto
  const raceData = props.race.race || props.race;
  
  console.log('Race data:', raceData);
  
  // Formato de las fechas para inputs HTML
  const formatDate = (dateString) => {
    if (!dateString) return '';
    
    // Si la fecha ya tiene el formato correcto YYYY-MM-DD, usarla directamente
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
      return dateString;
    }
    
    try {
      const date = new Date(dateString);
      // Verificar que sea una fecha válida
      if (isNaN(date.getTime())) return '';
      
      return date.toISOString().split('T')[0]; // YYYY-MM-DD
    } catch (error) {
      console.error("Error formateando fecha:", error);
      return '';
    }
  };
  
  // Formato de las horas para inputs HTML
  const formatTime = (timeString) => {
    if (!timeString) return '';
    
    // Si ya es un string con formato HH:MM o HH:MM:SS
    if (/^\d{2}:\d{2}(:\d{2})?$/.test(timeString)) {
      return timeString.substring(0, 5); // HH:MM
    }
    
    // Si es una fecha ISO completa
    if (timeString.includes('T')) {
      return timeString.split('T')[1].substring(0, 5);
    }
    
    try {
      // Intentar crear un objeto Date si es otro formato
      const time = new Date(`2000-01-01T${timeString}`);
      if (isNaN(time.getTime())) return '';
      
      return time.toTimeString().substring(0, 5); // HH:MM
    } catch (error) {
      console.error("Error formateando hora:", error);
      return '';
    }
  };
  
  // Rellenar el formulario con datos existentes, con formato seguro
  form.value = {
    name: raceData.name || '',
    circuit_id: raceData.circuit?.id || '',
    season_id: raceData.season?.id || '',
    round: raceData.round || '',
    race_date: formatDate(raceData.race_date),
    start_time: formatTime(raceData.start_time),
    practice1_date: formatDate(raceData.practice1_date),
    practice1_time: formatTime(raceData.practice1_time),
    practice2_date: formatDate(raceData.practice2_date),
    practice2_time: formatTime(raceData.practice2_time),
    practice3_date: formatDate(raceData.practice3_date),
    practice3_time: formatTime(raceData.practice3_time),
    qualifying_date: formatDate(raceData.qualifying_date),
    qualifying_time: formatTime(raceData.qualifying_time),
    sprint_qualifying_date: formatDate(raceData.sprint_qualifying_date),
    sprint_qualifying_time: formatTime(raceData.sprint_qualifying_time),
    sprint_date: formatDate(raceData.sprint_date),
    sprint_time: formatTime(raceData.sprint_time),
    scheduled_laps: raceData.scheduled_laps || '',
    distance: raceData.distance || '',
    status: raceData.status || 'Scheduled',
    weekend_format: raceData.weekend_format || 'traditional'
  };
  
  // Debug: mostrar valores formateados en consola
  console.log('Fecha de carrera formateada:', formatDate(raceData.race_date));
  console.log('Hora de inicio formateada:', formatTime(raceData.start_time));
}

// Enviar formulario
function submitForm() {
  emit('submit', form.value);
}
</script>
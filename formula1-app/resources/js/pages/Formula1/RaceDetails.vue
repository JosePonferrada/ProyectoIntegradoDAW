<template>
  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header con información básica -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ race?.name }}</h1>
                <div class="flex items-center mt-2">
                  <CountryFlag v-if="race?.circuit?.country" :countryCode="race.circuit.country.code" class="h-5 w-7 mr-2" />
                  <span class="text-gray-600 dark:text-gray-300">{{ race?.circuit?.name }}, {{ race?.circuit?.country?.name }}</span>
                </div>
                <div class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                  {{ formatDate(race?.race_date) }}
                </div>
              </div>
            </div>
            
            <div class="mt-4 md:mt-0 flex flex-col items-start md:items-end">
              <div class="px-3 py-1 rounded-full text-xs font-medium" 
                   :class="getRaceStatusClass(race?.status)">
                {{ race?.status || 'Scheduled' }}
              </div>
              
              <div v-if="shouldShowCountdown(race)" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                {{ countdown }}
              </div>
            </div>
          </div>
        </div>
        
        

        <!-- Navegación por pestañas -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px space-x-8 px-4 sm:px-6" aria-label="Tabs">
              <a v-for="tab in tabs" 
                 :key="tab.id"
                 @click.prevent="currentTab = tab.id"
                 :class="[
                   currentTab === tab.id 
                     ? 'border-f1-red text-f1-red' 
                     : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600',
                   'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer'
                 ]">
                {{ tab.name }}
              </a>
            </nav>
          </div>
          
          <!-- Contenido de cada pestaña -->
          <div class="p-6">
            <!-- Info -->
            <RaceInfo v-if="currentTab === 'info'" 
                      :race="race" 
                      :isAdmin="isAdmin"
                      :countdown="countdown" />
            
            <!-- Results -->
            <RaceResults v-else-if="currentTab === 'results'" 
                         :race="race" 
                         :results="raceResults" 
                         :loading="loading.results" />
            
            <!-- Qualifying -->
            <QualifyingResults v-else-if="currentTab === 'qualifying'" 
                              :race="race" 
                              :results="qualifyingResults" 
                              :loading="loading.qualifying" />
            
            <!-- Circuit -->
            <CircuitView v-else-if="currentTab === 'circuit'" 
                           :circuit="race?.circuit" />
            
            <!-- Steward Decisions -->
            <StewardDecisions v-else-if="currentTab === 'decisions'"
                              :race="race" :isAdmin="isAdmin"/>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CountryFlag from '@/components/CountryFlag.vue';
import RaceInfo from '@/components/RaceDetails/RaceInfo.vue';
import RaceResults from '@/components/RaceDetails/RaceResults.vue';
import QualifyingResults from '@/components/RaceDetails/QualifyingResults.vue';
import CircuitView from '@/components/RaceDetails/CircuitView.vue';
import StewardDecisions from '@/components/RaceDetails/StewardDecisions.vue';
import axios from 'axios';

const props = defineProps({
  id: Number // Recibe el ID de la carrera desde el controlador de Inertia
});

const race = ref(null);
const raceResults = ref([]);
const qualifyingResults = ref([]);
const countdown = ref(null);
const countdownInterval = ref(null);

const tabs = [
  { id: 'info', name: 'Information' },
  { id: 'results', name: 'Race Results' },
  { id: 'qualifying', name: 'Qualifying' },
  { id: 'circuit', name: 'Circuit' },
  { id: 'decisions', name: 'Steward Decisions' }
];

const currentTab = ref('info');
const loading = ref({
  race: true,
  results: false,
  qualifying: false
});

// Verificar si el usuario es administrador
const isAdmin = computed(() => {
  const user = usePage().props.auth.user;
  return user && user.role === 'admin';
});

// Cargar los datos iniciales
onMounted(async () => {
  const raceId = props.id;
  loading.value.race = true;
  
  try {
    // Cargar datos de la carrera
    const response = await axios.get(`/api/races/${raceId}`);
    race.value = response.data.data;
    
    // Si la carrera está en Scheduled o Upcoming, iniciar contador
    if (shouldShowCountdown(race.value)) {
      startCountdown();
    }
    
  } catch (error) {
    console.error('Error cargando detalles de carrera:', error);
  } finally {
    loading.value.race = false;
  }
});

// Cargar datos según la pestaña seleccionada
watch(currentTab, async (newTab) => {
  const raceId = props.id;
  
  if (newTab === 'results' && raceResults.value.length === 0) {
    loading.value.results = true;
    try {
      const response = await axios.get(`/api/race-results?race_id=${raceId}`);
      raceResults.value = response.data.data;
    } catch (error) {
      console.error('Error cargando resultados de carrera:', error);
    } finally {
      loading.value.results = false;
    }
  }
  
  if (newTab === 'qualifying' && qualifyingResults.value.length === 0) {
    loading.value.qualifying = true;
    try {
      const response = await axios.get(`/api/qualifying-results?race_id=${raceId}`);
      qualifyingResults.value = response.data.data;
    } catch (error) {
      console.error('Error cargando resultados de clasificación:', error);
    } finally {
      loading.value.qualifying = false;
    }
  }
});

// Limpiar el intervalo al desmontar
onUnmounted(() => {
  if (countdownInterval.value) {
    clearInterval(countdownInterval.value);
  }
});

// Determinar si debe mostrar la cuenta atrás
function shouldShowCountdown(race) {
  if (!race || !race.race_date) return false;
  
  const now = new Date();
  const raceDate = new Date(race.race_date);
  const status = race.status?.toLowerCase() || '';
  
  return raceDate > now && 
         (status === 'scheduled' || status === 'upcoming' || status === 'confirmed');
}

// Iniciar cuenta atrás
function startCountdown() {
  countdownInterval.value = setInterval(() => {
    const now = new Date();
    const raceDate = new Date(race.value.race_date);
    const diff = raceDate - now;
    
    if (diff <= 0) {
      clearInterval(countdownInterval.value);
      countdown.value = "Race starting now";
      return;
    }
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    countdown.value = `${days}d ${hours}h ${minutes}m ${seconds}s`;
  }, 1000);
}

// Formatear fecha
function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  });
}

// Obtener la clase CSS basada en el estado
function getRaceStatusClass(status) {
  if (!status) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
  
  const statusLower = status.toLowerCase();
  
  if (statusLower === 'completed' || statusLower === 'finished') {
    return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
  }
  
  if (statusLower === 'in progress' || statusLower === 'ongoing' || statusLower === 'live') {
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
  }
  
  if (statusLower === 'cancelled' || statusLower === 'postponed') {
    return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
  }
  
  return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; // Scheduled, Upcoming, Confirmed
}
</script>